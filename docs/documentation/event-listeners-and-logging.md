---
layout: default
title: documentation | event listeners and logging | InterpretersOffice.org

---

# Event listeners and logging

The [Laminas MVC framework](https://docs.laminas.dev/mvc/) describes itself as event-driven, and <span class="text-monospace">InterpretersOffice</span> 
likewise uses -- perhaps even overuses -- both the Laminas and the [Doctrine event](https://www.doctrine-project.org/projects/doctrine-event-manager/en/latest/index.html) systems. 
The use of Laminas' event system is described elsewhere in this documentation. For example, event listeners are used to enforce [user authentication 
and authorization](./request-cycle.html#authentication-and-authorization). <span class="text-monospace">InterpretersOffice</span> also takes advantage 
of Doctrine's entity event listeners for tasks such as clearing caches following write operations, ensuring that metadata properties of certain entities 
are set correctly, and logging noteworthy events.

<div class="alert alert-info rounded border border-primary shadow-sm p-3">
<strong>Point of terminology:</strong>  we speak of events in the sense of event listeners, and <span class="text-monospace">Event</span>s in the sense 
of Doctrine entities representing an event in the judiciary system that requires a court interpreter. The latter variety is differentiated 
by capitalization and the use of a <span class="text-monospace">monospace</span> font.
</div>

Most of our Doctrine listeners are located in 
[<span class="text-monospace text-nowrap">module/InterpretersOffice/src/Entity/Listener</span>](https://github.com/davidmintz/court-interpreters-office/tree/master/module/InterpretersOffice/src/Entity/Listener)
Some are registered via configuration and others are registered at runtime. In the latter case, we usually register the listener in the 
factory class of the relevant controller(s). It would be more convenient but less efficient to do this just once in the module's 
<span class="text-monospace">onBootstrap()</span>, because at that stage we don't know whether we are going to need the event listener. By way 
of example from the [admin EventsController factory](https://github.com/davidmintz/court-interpreters-office/blob/master/module/Admin/src/Controller/Factory/EventsControllerFactory.php):

<pre><code class="language-php line-numbers">
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $auth = $container->get('auth');
        $em = $container->get('entity-manager');
        $updateManager = $container->get(ScheduleUpdateManager::class);
        
        $controller = new EventsController(
            $em,
            $auth,
            $updateManager
        );

        // attach the entity listeners
        $resolver = $em->getConfiguration()->getEntityListenerResolver();
        $resolver->register($container->get(Listener\EventEntityListener::class));
        $resolver->register($container->get(Listener\UpdateListener::class)->setAuth($auth));

        $sharedEvents = $container->get('SharedEventManager');
        $sharedEvents->attach(
            EventsController::class,
            'deleteEvent',
            [$updateManager,'onDeleteEvent']
        );
        // etc
    }
</code></pre>

The above illustrates how the <code class="language-php">$container</code> provides access to the dependencies required by the 
<span class="text-monospace">EventsController</span> as well as the objects we need in order to register the Doctrine entity listeners.
These listeners themselves are of course produced by factories according to the mapping found in 
[<span class="text-monospace">module/InterpretersOffice/config/module.config.php</span>]({{site.data.vars.github}}/module/InterpretersOffice/config/module.config.php).

<!-- 
{% highlight javascript %}
console.log('alert');
{% endhighlight %} 
-->

As a further example, let's take one of the callback methods from the [<span class="text-monospace">EventEntityListener</span>]({{site.data.vars.github}}/module/InterpretersOffice/src/Entity/Listener/EventEntityListener.php):

<pre><code class="language-php line-numbers">
/**
* preUpdate callback
*
* @param Entity\Event $entity
* @param PreUpdateEventArgs $args
*/
public function preUpdate(
   Entity\Event $entity,
   PreUpdateEventArgs $args
) {

   $fields_updated = $this->reallyModified($entity, $args);
   $user = $this->getAuthenticatedUser($args);   
   $id = $entity->getId();
   if (in_array('deleted', $fields_updated) && $entity->getDeleted()) {
       if ((string)$user->getRole() !== 'submitter') {
           $message = sprintf(
               'user %s deleted event #%d from the schedule',
               $user->getUsername(),
               $id
           );
           $this->logger->info(
               $message,
               ['entity_class' => Entity\Event::class,'entity_id' => $id,'channel' => 'scheduling', ]
           );
       }
   }
   if ($fields_updated) {
       $entity->setModified($this->now);
       $entity->setModifiedBy($user);
       $cancellation_status_changed = false;   
       /* 
       now the interesting part: guessing the criteria for resetting the 
       sent_confirmation_email property of the related InterpreterEvents;
       */
       if (count($entity->getInterpreterEvents())) {
           $this->logger->debug(sprintf("there are %d interpreter_events",count($entity->getInterpreterEvents())));
           $changeset = $args->getEntityChangeSet();
                     
           if (in_array('cancellation_reason',array_keys($changeset))) {                    
               // if it's going from null to not null or vice-versa, that's significant
               $before_and_after = $changeset['cancellation_reason'];
               if (!$before_and_after[0] or !$before_and_after[1]) {
                   $cancellation_status_changed = true;   
               }
           }
           if ($cancellation_status_changed  or in_array('date',$fields_updated) 
               or in_array('time',$fields_updated)
           ) {  // turn off sent_confirmation_email for related InterpreterEvent entities                   
               $em = $args->getEntityManager();                   
               $dql = 'UPDATE InterpretersOffice\Entity\InterpreterEvent ie 
                   SET ie.sent_confirmation_email = false WHERE ie.event = :event';
               $result = $em->createQuery($dql)->setParameters(['event'=>$entity])->getResult();
               $who = implode('; ',array_map(function($i){return "{$i->getFullname()} <{$i->getEmail()}>";},
                   $entity->getInterpreters()));
               $log_message = sprintf(
                   'user %s changed the %s of event #%d; email confirmation status set to FALSE for %d interpreter(s): %s',
                   $user->getUsername(), implode(', ',$fields_updated),$id, $result, $who
               );
               $this->logger->info($log_message,
                   ['entity_class' => Entity\Event::class,'entity_id' => $id,'channel' => 'scheduling', ]
               );
           } else {
               $this->logger->debug("found interpreters assigned, not changing confirmation status");
           }
       }           
   }        
}
</code></pre>
Doctrine fires this callback at the *preUpdate* stage of its transactional database work. The first thing we have to do (at line 13) is use a helper 
method to work around a quirk of Doctrine that makes it think entity properties that are PHP DateTime objects have been changed when in reality they 
have not (this happens because the before-and-after objects are not identical, though they may be equivalent).

One of our objectives is to set the 
metadata properties *modified* and *modifiedBy* on the entity. The first is a PHP DateTime (which of course Doctrine converts to the appropriate database data type when the time comes), a timestamp 
equal to the current date and time; the second is a <span class="text-monospace">User</span>  entity -- i.e., the <span class="text-monospace">User</span> 
who is updating this <span class="text-monospace">Event</span>.

Another task performed here is writing to the application log. In this instance, we take note of the fact that the user has (soft-)deleted 
this <span class="text-monospace">Event</span>.

The next bit is unintelligible without a bit of background. The (human) users of <span class="text-monospace">InterpretersOffice</span> like a 
feature that tracks whether or not an interpreter assigned to an <span class="text-monospace">Event</span> has been notified via email about significant developments affecting 
the assignment. In their view of the schedule, a green check adjacent to an interpreter's name means this notification has been done. When the user 
sends email (through <span class="text-monospace">InterpretersOffice</span>) to the interpreter in regard to the assignment, the application 
considers which boilerplate email template is being used to decide whether set the interpreter-event entity's *email_confirmation_sent* field
(currently a boolean, although it would make more sense if it were a DateTime... another item for the next minor version update).

The reverse problem is knowing when to undo that setting, i.e., flag the interpreter-event as *not* having been notified of the latest *significant* modification 
to the event. The logic beginning at line 48 addresses this problem.
<hr>
The foregoing example is just one of many throughout the application. The intention is to give some sense of how the <span class="text-monospace">InterpretersOffice</span> is put together.

### logging

<span class="text-monospace">InterpretersOffice</span> uses the [<span class="text-monospace text-nowrap">Laminas\Log\Logger</span>](https://docs.laminas.dev/laminas-log/intro/) component 
for logging. Two log writers are involved: one that writes to a disk file, another that writes to the database. The log records application 
errors as well as a variety of user actions including logging in and out, creating/deleting/updating entities, and sending email messages. One 
interesting aspect of the logging system is that where possible, it notes the entity class and the entity id as well as a textual description 
of what is happening. This would make it reasonably easy to create a feature -- yet to be implemented -- whereby users can examine log events, with 
hypertext links to the entities that are involved. Another field in log records is a "channel" -- a simple string, not normalized in the database sense -- 
that serves as a sort of tag that can be used for filtering records, in addition to the standard [log severity levels](https://en.wikipedia.org/wiki/Syslog#Severity_level).

The log files are stored in the <span class="text-monospace text-nowrap">data/log</span> subdirectory and named in the format <span class="text-monospace text-nowrap">app.log.YYYY-MM-DD</span> 
indicating the date. You may want to set a cron job to archive these somehow, or rotate them out periodically so they don't consume an indefinite 
amount of disk space. As we observed in the section on [setting up the application](./setup.html), this directory has to exist and be writeable by the 
server, or else we throw an Exception.

The database log writer is [<span class="text-monospace text-nowrap">module/Admin/src/Service/Log/Writer.php</span>]({{site.data.vars.github}}/module/Admin/src/Service/Log/Writer.php).
It bypasses Doctrine to execute database inserts as quickly and simply as possible, using the same PDO object wrapped by Doctrine, and it writes to 
the <span class="text-monospace">app_event_log</span> table. 



