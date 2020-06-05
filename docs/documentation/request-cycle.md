---
layout: default
title: documentation | request cycle and application code organization | InterpretersOffice.org
---

# Request cycle and code organization

<span class="text-monospace">InterpretersOffice</span> uses the [Model-View-Controller](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
pattern and is built on the [Laminas MVC framework](https://docs.laminas.dev/mvc/), formerly know as Zend. Laminas/Zend now seems to be less 
popular than some other PHP frameworks around, notably Laravel and Symfony IV. The MVC pattern also seems to be passé, having been overtaken 
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
dependency injection container (a/k/a [ServiceManager](https://docs.laminas.dev/laminas-servicemanager/)), followed by *routing* 
the request. Routes -- the paths in the URL -- are mapped to *controllers* and their *action* methods via configuration. The 
framework figures out which controller class to instantiate, and then which of the controller's action methods to invoke.

So, for example, when a user navigates to <span class="text-monospace">/admin/schedule</span>, 
the framework looks to its configuration for a matching route, 
and [finds one](https://github.com/davidmintz/court-interpreters-office/blob/f9ebd8ea438694d7b99d98770ea176fb4b10155e/module/Admin/config/routes.php#L140). 
The framework then examines that configuration to see what controller to instantiate -- in this case, [<text class="text-monospace text-nowrap">InterpretersOffice\Admin\Controller\ScheduleController</text>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/Admin/src/Controller/ScheduleController.php)
-- and invokes the appropriate method on it, in this case <span class="text-monospace">scheduleAction()</span>.

<div class="border border-info rounded sm-shadow py-2 bg-light px-3 mb-3">
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
found in <span class="text-monospace">template_map.php</span>, Laminas would the look -- assume we're starting from the application root -- 
for <span class="text-monospace">module/Admin/view/interpreters-office/admin/schedule.phtml</span>. If that also fails, Laminas throws an Exception.

Now let's go back and look at this same example again with a little more detail, and with emphasis on how directories and files are laid out.

### the application root directory

The top level of the application directory looks like 

<pre class="bg-dark text-white p-2"><code>
    david@lin-chi:/opt/www/court-interpreters-office$ tree -La 1
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
is required for installation and occasional maintance, but not at runtime.

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

<span class="text-monospace text-nowrap">application.config.php</span> contains settings that the Framework uses for bootstrapping, and 
which you will rarely if ever need to change.

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
but, again, in the normal course there will be no reason to do so.) It also reads <span class="text-monospace text-nowrap">*global.php but 
the local files take precedence.</span>

The framework also reads in the configurations found in each individual module's <span class="text-monospace text-nowrap">config</span> 
subdirectory. Collecting and merging all this involves considerable overhead, which is why in production environments we enable configuration 
caching. **If you change the config you will need to purge the cache.** Forget this important detail, and you will go insane wondering 
why your update is having no effect.

Let's jump back up to the top level and continue.

The <span class="text-monospace text-nowrap">data</span> directory must be server-writeable. It contains, among other things, 
log files, a <span class="text-monospace text-nowrap">cache</span> subdirectory and the filesystem cache used by Doctrine ORM.

<span class="text-monospace text-nowrap">docs</span> contains documentation files, such as the one you are now reading. 
<span class="text-monospace text-nowrap">.eslintrc.json</span> is just for configuring [eslint](https://eslint.org/) tool, which is for 
tidying up Javascript code. The files <span class="text-monospace text-nowrap">.git</span>  <span class="text-monospace text-nowrap">.gitignore</span> 
are related to <span class="text-monospace text-nowrap">git</span>. <span class="text-monospace text-nowrap">LICENSE</span> 
is self-explanatory; the <span class="text-monospace text-nowrap">*.xml</span> files are for configuring phpunit and phpcs, the executables for which 
are located in <span class="text-monospace text-nowrap">vendor/bin</span>. At this writing <span class="text-monospace text-nowrap">test</span> contains some 
experimental QA-type tests written in Javascript. The phpunit tests can be found in each modules <span class="text-monospace text-nowrap">test</span> 
subdirectory. <span class="text-monospace text-nowrap">.travis.yml</span> is required in order to use the incredibly helpful [Travis CI](https://travis-ci.org/) 
service. None of these resources are required at runtime.

We've intentionally skipped a couple of items in order to save them for last: the <span class="text-monospace text-nowrap">public</span> 
and <span class="text-monospace text-nowrap">module</span> directories.

### the public subdirectory

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

### the module subdirectory

[to be continued...]

Let's assume the user requests <span class="text-monospace">/admin/schedule</span>, the authentication and authorization checks out, and 
the framework figures out which action of which controller of which module to dispatch. We mentioned earlier that during bootstrapping, 
Laminas initializes a *service manager*. The framework uses this service manager to instantiate controllers via factory classes,  i.e., 
classes that implement <span class="text-monospace">Laminas\ServiceManager\Factory\FactoryInterface</span>. A standard practice is to write 
configuration in a module's <span class="text-monospace">module.config.php</span> mapping controllers to factories. Looking at the 
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
view for purposes of display logic, but this detail is not particularly important for purposes of this discussion. The 
main point is that the controller factories have access, via the service manager a/k/a <code class="language-php">$container</code>,
 to whatever dependencies the controller requires. 


 [to be continued...]
 





