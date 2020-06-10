---
layout: default
title: documentation | request cycle and application code organization | InterpretersOffice.org
---

# Request cycle and code organization

<span class="text-monospace">InterpretersOffice</span> uses the [Model-View-Controller](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
pattern and is built on the [Laminas MVC framework](https://docs.laminas.dev/mvc/), formerly know as Zend. Laminas/Zend now seems to be less 
popular than some other PHP frameworks around, notably Laravel and Symfony 4. The MVC pattern also seems to be becoming passé, having been overtaken 
by the middleware paradigm. Laminas MVC is nevertheless a reasonable choice -- a quality framework with a strong user community 
where you can find support if you need it.

This document won't attempt to describe the intricacies of the Laminas MVC request/response cycle. The point is to provide a 
general outline of the application flow and in particular, where in our application directory tree 
you will find the physical files involved in that flow.

As with most modern PHP frameworks, the entry point to the application is its main
[index.php](https://github.com/davidmintz/court-interpreters-office/blob/master/public/index.php) file. The one in this 
application is straight out of the box from the Laminas skeleton application, there being no reason to modify it. Every http request 
is subject to [URL-rewriting rules](https://github.com/davidmintz/court-interpreters-office/blob/master/public/.htaccess) (also borrowed 
from Laminas without modification). If a URL matches an existing physical path, e.g., a Javascript or CSS resource, the request is 
served and we're done; otherwise it is handled by index.php, and the application takes over.

### routing

Many things happen under the hood during the application bootstrap phase, including reading configuration data, setting up the 
dependency injection container (a/k/a [Service Manager](https://docs.laminas.dev/laminas-servicemanager/)), followed by *routing* 
the request. Routes -- the paths in the URL -- are mapped to *controllers* and their *action* methods via configuration. The 
framework figures out which controller class to instantiate, and then which of the controller's action methods to invoke.

So, for example, when a user navigates to <span class="text-monospace">/admin/schedule</span>, 
the framework looks to its configuration for a matching route, 
and [finds one](https://github.com/davidmintz/court-interpreters-office/blob/f9ebd8ea438694d7b99d98770ea176fb4b10155e/module/Admin/config/routes.php#L140). 
The framework then examines that configuration to see what controller to instantiate -- in this case, [<text class="text-monospace text-nowrap">InterpretersOffice\Admin\Controller\ScheduleController</text>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/Admin/src/Controller/ScheduleController.php)
-- and invokes the appropriate method on it, in this case <span class="text-monospace">scheduleAction()</span>.

<div class="border border-info rounded sm-shadow py-3 bg-light px-3 mb-3">
    This is an oversimplification. Earlier in the request cycle, an event listener looks at the incoming request and makes
    decisions about authentication and authorization -- more about that in the 
    <a href="/documentation/authentication-and-authorization.html">section on authentication and authorization</a>.
</div>

To continue with this example, once we get into <span class="text-monospace">scheduleAction()</span> method, the flow of execution is typical 
of GET requests in this application. We fetch desired data from the database (in this case, an array of <span class="text-monospace">Event</span> 
entities matching a particular date, and related data); we inject this data into a <span class="text-monospace text-nowrap">Laminas\View\Model\ViewModel</span> 
object; and return it. The framework then takes care of rendering and outputting this to the browser as finished HTML, using a *viewscript* that 
we have written for this purpose. The viewscripts used by <span class="text-monospace">InterpretersOffice</span> are standard PHP files 
with a <span class="text-monospace">.phtml</span> extension.

You may be wondering how the Laminas framework figures out where to find these viewscript files. The short version is that a configuration file tells it where to look, 
and there is more than one way to configure it.

We should mention here that like many Laminas MVC apps, <span class="text-monospace">InterpretersOffice</span> consists of 
multiple MVC *modules.* Technically, the only two essential modules are the ones in the <span class="text-monospace">InterpretersOffice</span> 
and <span class="text-monospace">InterpretersOffice\Admin</span> namespaces. (In practice, for the Southern District of New York where the app was developed, 
the <span class="text-monospace">InterpretersOffice\Requests</span> module is indispensable.) Each module lives in its own subdirectory and has, among other things, 
its own configuration files. Among these is a <span class="text-monospace">module.config.php</span> file which 
returns an array of configuration data. A common practice is to include here an array under the key <span class="text-monospace">view_manager</span> that tells 
Laminas how to resolve viewscripts for the module. 

<pre><code class="language-php">'view_manager' => [
    'template_map' => include(__DIR__.'/template_map.php'),
    'template_path_stack' => [
        __DIR__.'/../view',
    ],
],</code></pre>

The above configuration directive tells Laminas to look first at the configuration array returned by the file <span class="text-monospace">template_map.php</span>,
which maps controller actions to viewscripts. (The optional template map offers the advantage of performance at the cost of a bit more maintenance.) 
Then, if that fails, the fallback is to look in the module's <span class="text-monospace">view</span> subdirectory based on the convention 
<span class="text-monospace">module_name/controller_name/action_name.phtml</span>. Thus, in this example, if no mapping were to be 
found in <span class="text-monospace">template_map.php</span>, Laminas would then look for 
<span class="text-monospace">module/Admin/view/interpreters-office/admin/schedule.phtml</span>. If that also fails, Laminas throws an Exception.

Now let's go back and look at this same example again with a little more detail, and emphasize how directories and files are laid out.

### the application root directory

The top level of the application directory looks like 

<pre class="bg-dark text-white p-2"><code>
    david@lin-chi:/opt/www/court-interpreters-office$ tree -aL 1
    .
    ├── bin
    ├── composer.json
    ├── composer.lock
    ├── config
    ├── data
    ├── docs
    ├── .eslintrc.json
    ├── .git
    ├── .gitignore
    ├── LICENSE
    ├── module
    ├── phpcs.xml
    ├── phpdoc.dist.xml
    ├── phpunit.xml
    ├── public    
    ├── README.md
    ├── test
    ├── .travis.yml
    └── vendor    
</code></pre>

<span class="text-monospace text-nowrap">bin</span> contains some interesting utilities but none of them is required at runtime.

The <span class="text-monospace text-nowrap">composer.*</span> files are related to the PHP dependency manager PHP [Composer](https://getcomposer.org), which 
is required for installation and occasional maintenance, but not at runtime.

### configuration files

<span class="text-monospace text-nowrap">config</span> is, as you would expect, where configuration is stored. Its contents look like 

<pre class="bg-dark text-white p-2"><code>
david@lin-chi:/opt/www/court-interpreters-office$ tree -L 2 config
config
├── application.config.php
├── autoload
│   ├── config.local.php.dist
│   ├── doctrine.local.php
│   ├── doctrine.local.php.dist
│   ├── doctrine.test.php
│   ├── global.php
│   ├── local.development.php
│   ├── local.development.php.dist
│   ├── local.production.php
│   ├── local.production.php.dist
│   ├── local.staging.php
│   ├── local.testing.php
│   ├── mailgun.sdny.local.php
│   ├── navigation.global.php
│   ├── README.md
│   ├── rotation_module.local.php
│   └── vault.local.php
├── cli-config.php
├── development.config.php -> development.config.php.dist
├── development.config.php.dist
├── doctrine-bootstrap.php
└── modules.config.php
</code></pre>

<span class="text-monospace text-nowrap">application.config.php</span> contains settings that Laminas uses 
early in the bootstrapping phase, and which you will rarely if ever need to change.

In the <span class="text-monospace text-nowrap">autoload</span> subdirectory, everything with a <span class="text-monospace text-nowrap">.dist</span> 
extension is a sort of template that ships with the application. At installation time these are copied (manually, in the current version of 
<span class="text-monospace">InterpretersOffice</span>) to their equivalents minus the <span class="text-monospace text-nowrap">.dist</span> and 
edited according to the local environment. The purpose of "development," "production", etc., is to enable us to configure the application 
differently depending on the environment variable *environment*, which is is set in the vhost configuration, and can be read 
with the PHP <span class="text-monospace text-nowrap">getenv()</span> function.

<div class="border border-info rounded sm-shadow py-2 bg-light px-3 mb-3">
    Any filename with "local" in it is intended for local use only and <em>is not stored in the github repository.</em> This is 
    where we put sensitive configuration data like database passwords.
</div>

The framework consumes everything in <span class="text-monospace text-nowrap">autoload</span> that matches the environment or that contains 
"local" in its name. (You can control this behavior by tweaking a setting in <span class="text-monospace text-nowrap">application.config.php</span> 
but, again, in the normal course there will be no reason to do so.) It also reads <span class="text-monospace text-nowrap">*global.php</span> but 
the local files take precedence.

The framework also reads in the configurations found in each individual module's <span class="text-monospace text-nowrap">config</span> 
subdirectory. Collecting and merging all this involves considerable overhead, which is why in production environments we enable configuration 
caching. **If you change the config you will need to purge the cache.** Forget this important detail, and you will go insane wondering 
why your update is having no effect.

Let's jump back up to the top level and continue.

The <span class="text-monospace text-nowrap">data</span> directory must be server-writeable. It contains, among other things, 
log files, a <span class="text-monospace text-nowrap">cache</span> subdirectory and the filesystem cache used by Doctrine ORM.

<span class="text-monospace text-nowrap">docs</span> contains documentation files, such as the one you are now reading. 
<span class="text-monospace text-nowrap">.eslintrc.json</span> is just for configuring [eslint](https://eslint.org/), a tool for 
tidying up Javascript code. The files <span class="text-monospace text-nowrap">.git</span>  <span class="text-monospace text-nowrap">.gitignore</span> 
are related to <span class="text-monospace text-nowrap">git</span>. <span class="text-monospace text-nowrap">LICENSE</span> 
is self-explanatory; the <span class="text-monospace text-nowrap">*.xml</span> files are for configuring phpunit and phpcs, the executables for which 
are located in <span class="text-monospace text-nowrap">vendor/bin</span>. At this writing <span class="text-monospace text-nowrap">test</span> contains some 
experimental QA-type tests written in Javascript. The phpunit tests can be found in each modules <span class="text-monospace text-nowrap">test</span> 
subdirectory. <span class="text-monospace text-nowrap">.travis.yml</span> is required in order to use the incredibly helpful [Travis CI](https://travis-ci.org/) 
service. None of these resources are required at runtime.

The <span class="text-monospace text-nowrap">vendor</span> directory contains a copious amount of third-party libraries, including 
the Laminas framework itself. <span class="text-monospace text-nowrap">composer</span> creates and manages this; no one else should 
modify any of its contents.

We've intentionally skipped a couple of items in order to save them for last: the <span class="text-monospace text-nowrap">public</span> 
and <span class="text-monospace text-nowrap">module</span> directories.

### the public resources

<span class="text-monospace text-nowrap">public</span> is the web document root. It has the application entry point, <span class="text-monospace text-nowrap">index.php</span>, 
and all our Javascript and CSS assets are in <span class="text-monospace text-nowrap">js</span> and <span class="text-monospace text-nowrap">css</span>, respectively. 
Several third-party js and CSS libraries are located in here as well. Most of our CSS is provided by [Bootstrap](https://getbootstrap.com); 
the custom CSS is minimal.

The custom Javascript, however, is abundant. A naming convention strongly suggests which js files are combined with which controller actions, 
but the places to look, if you want to identify the js that is used on a given page, are in the main 
[layout viewscript](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/view/layout/layout.phtml) and in the 
individual action's viewscript. The layout loads all the js that is nearly certain to be needed down the line -- mostly libraries. The 
individual viewscripts load js code that is specific to the particular page/controller-action. Within those viewscripts, we load js resources 
by using a view helper that ships with Laminas, e.g., 

<pre><code class="language-php"><?php /* module/Admin/view/interpreters-office/admin/schedule/index.phtml */
    
    $this->headScript()->appendFile($this->basePath('js/lib/jquery-ui/jquery-ui.min.js'))
        ->appendFile($this->basePath('js/lib/moment/min/moment.min.js'))
        ->appendFile($this->basePath('js/admin/schedule.js'));
        // etc
</code></pre>

### the modules

Virtually all <span class="text-monospace">InterpretersOffice</span>'s server-side PHP reside in <span class="text-monospace text-nowrap">module</span>. 
Tbe standard modular structure of Laminas MVC is neatly organized:
<pre class="bg-dark text-white p-2"><code>
    david@lin-chi:/opt/www/court-interpreters-office$ tree -L 2 module
    module
    ├── Admin
    │   ├── config
    │   ├── src
    │   ├── test
    │   └── view
    ├── InterpretersOffice
    │   ├── config
    │   ├── src
    │   ├── test
    │   └── view
    ├── Notes
    │   ├── config
    │   ├── src
    │   ├── test
    │   └── view
    ├── Requests
    │   ├── config
    │   ├── src
    │   ├── test
    │   └── view
    ├── Rotation
    │   ├── config
    │   ├── src
    │   ├── test
    │   └── view
    └── Vault
        ├── config
        ├── src
        └── test
</code></pre>
The mapping of these physical paths to PHP namespaces is defined in <span class="text-monospace text-nowrap">composer.json</span>. The various 
module subdirectories have short names but correspond to longer, fully qualified namespaces. The <span class="text-monospace text-nowrap">Admin</span> 
module contains code in the <span class="text-monospace text-nowrap">InterpretersOffice\Admin</span> namespace; the <span class="text-monospace text-nowrap">Requests</span> 
maps to the <span class="text-monospace text-nowrap">InterpretersOffice\Requests</span> namespace; and so on.

<div class="border border-info rounded sm-shadow py-3 bg-light px-3 mb-3">    
    As mentioned earlier, only the <span class="text-monospace text-nowrap">InterpretersOffice</span> and <span class="text-monospace text-nowrap">InterpretersOffice\Admin</span>
    modules are literally <em>sine qua non</em>; the application will not run unless they are enabled. Note that in the SDNY Interpreters Office, however, the module 
    <span class="text-monospace text-nowrap">InterpretersOffice\Requests</span> is <em>de facto</em> required. It provides the features whereby Court personnel 
    other than those in the Interpreters Office can register user accounts, log in, and create, view, update, and withdraw requests for interpreting services. 
    Pursuant to official court policy it is <em>the</em> preferred means of scheduling interpreters. Any other District Court wishing to adopt 
    <span class="text-monospace">InterpretersOffice</span> can opt in or out of using <span class="text-monospace text-nowrap">InterpretersOffice\Requests</span>.
</div>

Modules are enabled or disabled by editing 
[<span class="text-monospace text-nowrap">config/modules.config.php</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/config/modules.config.php), 
and in practice you will rarely need to touch it. The order in which modules are loaded is significant. The first several entries are for the 
Laminas framework and Doctrine ORM, while the later entries refer to the modules that compose the actual <span class="text-monospace">InterpretersOffice</span>
application. The <span class="text-monospace text-nowrap">InterpretersOffice</span> and <span class="text-monospace text-nowrap">InterpretersOffice\Admin</span> modules 
must come first, in that order; the ones following depend on them.

All of these modules, with the exception of <span class="text-monospace text-nowrap">Vault</span>, are under the <span class="text-monospace">InterpretersOffice</span> 
namespace. Each module has its own <span class="text-monospace text-nowrap">config</span> and other subdirectories. This helps to keep the modules separate and self-contained. 
Each module's <span class="text-monospace text-nowrap">src</span> subdirectory contains module-specific classes: service classes that do especially heavy lifting, controllers, 
factory classes, form classes derived from <span class="text-monospace text-nowrap">Laminas\Form</span>, and more. The <span class="text-monospace text-nowrap">view</span> 
subdirectory is for viewscripts, as you might have guessed; and similarly, the <span class="text-monospace text-nowrap">test</span> subdirectory 
contains [phpunit](https://phpunit.de/) tests.

Each module's <span class="text-monospace text-nowrap">src</span> folder includes a class called <span class="text-monospace text-nowrap">Module.php</span>, which 
can optionally define an <span class="text-monospace text-nowrap">onBootstrap()</span> method, invoked early in the request cycle, where you can attach event 
listeners that may be triggered later in the cycle. On the plus side, it encourages separation of concerns, but it can get complicated. Internally, Laminas 
relies heavily on its [event system](https://docs.laminas.dev/laminas-mvc/mvc-event/)  and <span class="text-monospace">InterpretersOffice</span> uses it as well. 
When trying to comprehend the flow of execution you may find it helpful either to use a debugger or recursively grep <span class="text-monospace text-nowrap">src</span> folders for 
strings like "attach" and "trigger."

All that said, let's return to the example in which the user requests <span class="text-monospace text-nowrap">/admin/schedule</span>.

### authentication and authorization

The Admin module's <span class="text-monospace text-nowrap">onBootstrap()</span> method does some initialization work,
which includes attaching a listener to the <code class="language-php">MvcEvent::EVENT_ROUTE</code> event to enforce 
authentication and authorization.

<pre><code class="language-php">
public function onBootstrap(EventInterface $event)
{
    // ...
   $eventManager = $event->getApplication()->getEventManager();
   $eventManager->attach(MvcEvent::EVENT_ROUTE, [$this, 'enforceAuthentication']);
    // ...

}
</code></pre>

The above snippet tells the event manager to execute the <span class="text-monospace text-nowrap">enforceAuthentication()</span> method 
of [<span class="text-monospace text-nowrap">Admin\Module.php</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/Admin/src/Module.php) 
when the routing event has taken place -- in other words, once we know what module, controller and action are going to serve the request.

<span class="text-monospace text-nowrap">enforceAuthentication()</span> considers whether authentication is required (for a few routes, it is not).
If the user is not authenticated, and is requesting a *resource* that requires authentication, we redirect to the login page. If the user is authenticated, 
we next consider whether this user is *authorized* access to the requested resource, using a [service](https://github.com/davidmintz/court-interpreters-office/blob/master/module/Admin/src/Service/Acl.php) derived from the 
[Laminas ACL](https://docs.laminas.dev/laminas-permissions-acl/usage/) implementation and an extensive configuration file located at 
<span class="text-monospace text-nowrap">module/Admin/config/acl.php</span>. Note that other modules can contribute their 
own <span class="text-monospace">acl</span> settings as well, by placing the appropriate configuration in their own config files. We check
whether the *role* that the current user is authorized in relation to the requested *resource* (usually, a controller) and *privilege* (usually, an action). 

If the authorization is denied, we redirect to the login page with a message saying access denied.

For this example, assume the user is logged in and has the role *manager.* Per our ACL rules, a *manager* is allowed to run the 
<span class="text-monospace text-nowrap">schedule</span> action of the <span class="text-monospace text-nowrap">InterpretersOffice\Admin\ScheduleController</span>, 
so we move on.

### controller, action and view

We mentioned earlier that during bootstrapping, Laminas initializes a *service manager*. The framework uses this service manager to instantiate 
controllers via factory classes,  i.e., classes that implement <span class="text-monospace">Laminas\ServiceManager\Factory\FactoryInterface</span>. 
A standard practice is to write configuration in a module's <span class="text-monospace">module.config.php</span> mapping controllers to factories. Looking at the 
[<span class="text-monospace">ScheduleControllerFactory</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/Admin/src/Controller/Factory/ScheduleControllerFactory.php)'s
<span class="text-monospace">invoke__</span> method you'll notice that the first argument is an implementation 
of <span class="text-monospace">Interop\Container\ContainerInterface</span>. That <code class="language-php">$container</code> is 
the service manager, which (if properly set up) provides access to all the dependencies and configuration we need.


<pre><code class="language-php line-numbers">   /**
    * implements Laminas\ServiceManager\FactoryInterface
    *
    * @param ContainerInterface $container
    * @param string $requestedName
    * @param null|array $options
    * @return ScheduleController
    */
   public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
   {       
       $end_time_enabled = $this->getEndTimeEnabled();
       $em = $container->get('entity-manager');

       return new ScheduleController($em, ['end_time_enabled'=>$end_time_enabled]);
   }</code></pre>

Line 11 is concerned with getting a configuration datum to help the <span class="text-monospace">ScheduleController</span> configure the 
view for purposes of presentation logic, but this detail is merely incidental to this discussion. The main point is that the controller 
factories have access, via the service manager a/k/a <code class="language-php">$container</code>, to whatever dependencies 
the controller requires.

 This pattern recurs throughout the application. We usually don't instantiate objects, but rather pull them from the container. By default, 
 the container will re-use any already-existing instance, and again, the dependency injection is taken care of via factories 
 that we write and register with the module configuration. Conventionally, you identify classes by their fully qualified class names, or 
 other likewise unique identifiers, to avoid  ambiguity and collisions. But you can also set aliases for convenience. In the above example, 
 'entity-manager' is an alias for the more verbose <span class="text-monospace text-nowrap">doctrine.entitymanager.orm_default</span>

 Controllers are found in each module's <span class="text-monospace text-nowrap">src/Controllers</span> folder. The factories in most 
 cases are in <span class="text-monospace text-nowrap">src/Controller/Factory</span>, to keep <span class="text-monospace text-nowrap">src/Controllers</span> 
 from becoming too cluttered (with the smaller modules I decided it was not necessary to dedicate a folder to the factory classes, 
 so they sit alongside the controllers.)
 
 With a <span class="text-monospace text-nowrap">ScheduleController</span> having been instantiated by the framework, it now dispatches 
 the action matched by routing, in this case <span class="text-monospace text-nowrap">scheduleAction()</span>:
 <pre><code class="language-php line-numbers">
 public function scheduleAction()
    {
        $filters = $this->getFilters();
        $date = new \DateTime($filters['date']);
        $repo = $this->entityManager->getRepository(Entity\Event::class);
        $data = $repo->getSchedule($filters);
        $end_time_enabled = $this->config['end_time_enabled'];
        $viewModel = new ViewModel(compact('data', 'date','end_time_enabled'));
        $this->setPreviousAndNext($viewModel, $date)
            ->setVariable('language', $filters['language']);
        if ($this->getRequest()->isXmlHttpRequest()) {
            $viewModel
                ->setTemplate('interpreters-office/admin/schedule/partials/table')
                ->setTerminal(true);
        }
        return $viewModel;
    }
</code></pre>

This controller fetches interpreter scheduling data -- an array of <span class="text-monospace">Event</span> entities -- 
for a particular date. It uses a helper method to apply filters to the database query, collects a few other details, assigns these 
variables to a <span class="text-monospace text-nowrap">ViewModel</span>, and returns the <span class="text-monospace text-nowrap">ViewModel</span> 
object.

The <code class="language-php">$this->entityManager</code> in the above excerpt is the Doctrine entity manager that was passed 
to the controller's constructor back at the factory. Nearly all of the communication with the data layer is done through this object. 
In this example we use it for access to a custom repository class that knows how to fetch the schedule data. The bulk of our 
entity models and repositories are in <span class="text-monospace text-nowrap">module/InterpretersOffice/src/Entity</span>

Viewscripts are located in each module's <span class="text-monospace">view</span> subdirectory. The framework consults its configuration to determine
viewscript to use for rendering, as described above. In this example our viewscript is 
[<span class="text-monospace text-nowrap">module/Admin/view/schedule/schedule.phtml</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/Admin/view/schedule/schedule.phtml), 
which loads some Javascript for things like a datepicker for navigating by date and other interactive controls, then handles display 
logic to show the user the events on the interpreters' schedule for a given date.

<pre><code class="language-php"><?php  /** module/Admin/view/schedule/schedule.phtml */
    $this->headScript()->appendFile($this->basePath('js/lib/jquery-ui/jquery-ui.min.js'))
        ->appendFile($this->basePath('js/lib/moment/min/moment.min.js'))
        ->appendFile($this->basePath('js/admin/schedule.js'));
    $this->headTitle($this->date->format('D d M Y'));
    $messenger = $this->flashMessenger();
    if ($messenger->hasSuccessMessages()) :
        echo $messenger->render('success', ['alert','alert-success',], false);
    endif;
    // etc
</code></pre>

The <code class="php-language">$messenger</code> refers to a Laminas 
[controller plugin](https://docs.laminas.dev/laminas-mvc-plugin-flashmessenger/) with which you can set a message to show the 
user, then redirect to another page and display it. <span class="text-monospace">InterpretersOffice</span> uses this 
technique frequently for showing the user confirmation messages, sometimes with server-side redirection, sometimes client-side 
with Javascript by saying <code class="language-javascript">document.location = some_other_url</code>

<hr>

The preceding walk-through is intended to provide a feel for how a typical request/response cycle 
works, and where the various files are found in the application directory tree. This example is for a GET request, 
where the user is reading data as opposed to changing it.

The pattern for pages for creating or editing entities is typical web application flow. We display an HTML form; 
the user enters data in the form and POSTs it; we validate the input; if validation fails we display error messages; 
otherwise we update the database using the Doctrine API, and then display a confirmation. For the most part, the 
forms are built by extending [Laminas\Form](https://docs.laminas.dev/laminas-form/) components, which take an object-oriented 
approach for form elements, data validation and filtering.

For further information you can examine the source code, read the documentation sites for the framework and various libraries, 
or ask me a question:  david@davidmintz.org. 

 
 





