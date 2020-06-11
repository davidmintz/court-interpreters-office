---
layout: default
title: documentation | authentication and authorization | InterpretersOffice.org
---

# Authentication and authorization

There is a general description of how <span class="text-monospace">InterpretersOffice</span> handles 
authentication and authorization in the section on the Laminas MVC [request cycle](./request-cycle.html#authentication-and-authorization),
and in the section on the entity relationship model there is a discussion of the entities 
[<span class="text-monospace">Person</span>, <span class="text-monospace">User</span> and <span class="text-monospace">Role</span>](./data-model.html#people-and-hats-users-and-roles).
I suggest reading those before reading this.

### authentication

<span class="text-monospace">InterpretersOffice</span> currently uses single-factor user/password authentication, although this may change in a future version.
As an additional security measure, consecutive failed login attempts are counted, and the user account is disabled if a certain number of consecutive failures is 
reached. This number can be configured (as explained in the [setup guide](./setup.html#other-configuration)); the default is six. A disabled account can only be re-enabled by a user having the role *manager* or *administrator*.

The application also enforces a moderate password strength requirement. (I recognize that password strength policies are not a settled issue, with 
many sites refusing passwords that are in fact stronger than ones they accept. A truly good password strength policy is one that uses an [intelligent tool 
to check complexity](https://pypi.org/project/password-strength/) rather than enforcing a set of published rules that arguably help a potential attacker.)

The route to the login page is <span class="text-monospace">/login</span>, which is mapped to the 
<span class="text-monospace">loginAction()</span> of the 
[<span class="text-monospace text-nowrap">InterpretersOffice\Controller\AuthController</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/src/Controller/AuthController.php).
<span class="text-monospace">InterpretersOffice</span>'s authentication system uses the [<span class="text-monospace text-nowrap">Laminas\Authentication\AuthenticationService</span>](https://docs.laminas.dev/laminas-authentication/intro/)
with a [custom adapter](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/src/Service/Authentication/Adapter.php). 
That adapter uses the Doctrine entity manager to query the database for a user matching the supplied identity and password. Passwords at rest are of course 
hashed, using PHP's native hashing functions. If authentication is successful, the adapter creates a simple PHP <span class="text-monospace">stdClass</span> <code class="language-php">$user</code> object 
which the authentication service ultimately stores in the session.

Elsewhere in the application, we determine whether the user is logged in by examining the authentication service, which is accessed by way of the 
service manager (container). Wherever we need a controller or service class to be authentication-aware, we inject the authentication service instance 
either via the constructor or a setter.

Upon successful login, the <span class="text-monospace">loginAction</span> event is triggered. An event listener has been attached 
earlier in the request cycle, specifically in our [<span class="text-monospace text-nowrap">AuthControllerFactory</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/src/Controller/Factory/AuthControllerFactory.php),
where an [<span class="text-monospace text-nowrap">AuthenticationListener</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/src/Service/Listener/AuthenticationListener.php) is wired to observe the login event.
This listener's job is to update the *last_login* property of the user object and reset the failed login count to zero. It also logs the user authentication event.

### user account management

The [<span class="text-monospace text-nowrap">InterpretersOffice\Controller\AccountController</span>](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/src/Controller/AccountController.php) 
has action methods for handling new user registration, email verification, and resetting forgotten passwords.

Note that the only type of users who can register their own user accounts are those in the *submitter* role: users who submit requests for interpreting 
services by way of the [ <span class="text-monospace">Requests</span>](https://github.com/davidmintz/court-interpreters-office/tree/master/module/Requests) module. This module can be 
thought of as a front end for users to manage their requests, while the [<span class="text-monospace">Admin</span>](https://github.com/davidmintz/court-interpreters-office/tree/master/module/Admin) 
module is the administrative back end.



On the administrative side, users with administrative roles *administrator* and *manager* can only be created manually by users with the same or 
higher access levels. The apparent chicken-and-egg problem is solved with an interactive command line script for creating an initial administrative 
user when the application is set up (from the app root directory: <span class="text-monospace text-nowrap">bin/admin-cli  setup:create-admin-user</span>). 
The reasoning behind this design is that (1) administrative access should be tightly controlled, and (2) the number of admin users required is small, hence
manageable by hand (in the Southern District of New York's Interpreters Office there are nine admin users, and we're among the larger federal court interpreter offices in the US).

When a user leaves the organization, if the user has a data history then the record cannot be deleted outright because of foreign key constraints -- and 
because we don't want to obliterate history. Instead, the account should disabled. Currently this has to be done manually by an administrator/manager. If a user -- or for that matter, any entity -- has no 
data history, the user management interface displays a Delete button for deleting the underlying database record.

<div class="border border-info rounded sm-shadow py-3 bg-light px-3 mb-3">
    Leaving ghost accounts lying around is bad security practice. The SDNY Interpreters formerly had a cron job that would send weekly emails to 
    all users in the submitter role to show them what interpreters they had scheduled for the coming week and remind them to update as needed. A side effect 
    was that when user's email accounts were removed, the messages would bounce with an error message to the effect that there was no such user -- a reliable 
    indication that the user had probably left the organization and the IT department had removed the email account. We set up a second cron job, scheduled 
    to run a few minutes after the email-reminder task, to log into the Interpreters collective email account and search for these bounced emails in the inbox, 
    identify the user account, disable it, and then delete the bounced email message. It was elegant and convenient, and also good house-keeping. Unfortunately 
    this cron job broke when the organization shifted its email to a new platform. The point of the story is that a mechanism that somehow monitors and cleans 
    up unused accounts is a good idea.
</div>

By design, the admin user-management interface provides no means of setting or getting user password (and "getting" them would in any case be pointless because 
they are hashed). The only way users can reset their passwords is through the actions provided by the 
[<span class="text-monospace"></span>AccountController](https://github.com/davidmintz/court-interpreters-office/blob/master/module/InterpretersOffice/src/Controller/AccountController.php).

You can set any user's password from the command line with: <span class="text-monospace text-nowrap">bin/admin-cli admin:user-password {{ "<email_address> <password>" | escape }}</span>

### authorization

Please see the section dealing with authentication authorization in the discussion of [the request cycle](./request-cycle.md#authentication-and-authorization), which 
gives a fair overview. An additional point worth noting is that <span class="text-monospace">InterpretersOffice</span> does most 
of its authorization checking via the event listener attached in the <span class="text-monospace">Admin</span> module's <span class="text-monospace">onBootstrap()</span>
method. If authorization is denied, a message is logged to that effect. If the authorization is denied by this event listener, it means a user actually tried 
to navigate to a URL that is not authorized, and was turned away. In some other cases, we simply query the [ACL service](https://github.com/davidmintz/court-interpreters-office/blob/master/module/Admin/src/Service/Acl.php) to determine whether a particular button 
or link should be displayed to the user, but if the result is negative, the log message is likewise generated. Thus presence of "access denied" messages 
in the log does not necessarily mean anyone was trying to do something nefarious. In future versions we might tweak this behavior to make it more nuanced.

The authorization system confers only a few privileges on *administrator* that *manager* does not have. Among these is write-access to the configuration 
settings for the  <span class="text-monospace">Requests</span> module, available at <span class="text-monospace text-nowrap">/admin/configuration/requests</span>  which allows 
the user to control what event listeners will be triggered on various actions on the part of the submitters.

The main configuration file for access control is <span class="text-monospace text-nowrap">module/Admin/config/acl.php</span>; other ACL configuration may be 
found in other modules' <span class="text-monospace text-nowrap">config/module.config.php</span> files. Familiarity with [Laminas ACL](https://docs.laminas.dev/laminas-permissions-acl/usage/) is required 
in order to make any sense out of them.

At this writing, **ACL configuration is hard-coded** in the application. You can change it, but when you update the application you'll run
into merge conflicts or clobber your local changes unless precautions are taken. I may address this in a future version.



