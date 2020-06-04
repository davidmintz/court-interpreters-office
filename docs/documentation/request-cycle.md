---
layout: default
title: documentation | request cycle and application code organization | InterpretersOffice.org
---

# Request cycle and code organization

<span class="monospace">InterpretersOffice</span> uses the [Model-View-Controller](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
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
served; otherwise it is handled by index.php and the application takes over.

Many things now happen under the hood, including reading configuration data, setting up the dependency injection container
(a/k/a [ServiceManager](https://docs.laminas.dev/laminas-servicemanager/)), and *routing* the request. Routes -- the paths in the URL -- 
are mapped to *controllers* and their *action* methods via configuration. The framework figures out which controller class to instantiate, 
and then which of the controller's action methods to invoke.

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

To continue with this example, once we get into <span class="text-monospace">scheduleAction()</span> method, the flow of execution is typical of GET requests in this 
application. In this method, we fetch desired data from the database (in this case, an array of <span class="text-monospace">Event</span> entities matching a 
particular date, and related data), inject this data into a <span class="text-monospace text-nowrap">Laminas\View\Model\ViewModel</span> object, and return it. 
The framework then takes care of rendering and outputting this to the browser as finished HTML, using a *viewscript* that we have written for this purpose. 
The viewscripts <span class="text-monospace">InterpretersOffice</span> uses are standard PHP files with a <span class="text-monospace">.phtml</span> extension.

How does the Laminas framework figure out where to find the viewscript? The short version is that a configuration file tells it where to look, and there is more 
than one way to set it up. We should mention here that like many Laminas MVC apps, <span class="text-monospace">InterpretersOffice</span> consists of 
multiple MVC *modules.* The only two essential modules are the ones in the <span class="text-monospace">InterpretersOffice</span> 
and <span class="text-monospace">InterpretersOffice\Admin</span> namespaces (although in practice, although technically the application can run 
without it, the <span class="text-monospace">InterpretersOffice\Requests</span> module is indispensable). Each module lives in its own subdirectory 
and has, among other things, its own configuration files. Among these is a <span class="text-monospace">module.config.php</span> file which 
returns an array of configuration data. Standard practice is to include here an array under the key <span class="text-monospace">view_manager</span> that tells 
Laminas how to resolve viewscripts for the module. 


[to be continued]




