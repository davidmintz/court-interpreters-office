---
layout: default
title: faq | InterpretersOffice.org
---

# faq

*This is very much a work in progress. And the F in FAQ is something of a misnomer; any question that's been asked more than zero times 
is deemed frequent. The following are predominantly system-administration-oriented, as you can see.*

### How do I create a user account?

It depends on what role the user is going to have. Users who are going to be requesting interpreters, as opposed to managing the administrative back end,
 have the role <em>submitter</em>. They create their own accounts by going to <span class="text-monospace">/user/register</span>, submitting the form, 
 and following the link contained in an email verfication message to activate their accounts. (As noted in the 
 [setup documentation](/documentation/setup.html#other-configuration), you can constrain the email  domains that are permitted via configuration.)

The other roles are <em>administrator</em>, <em>manager</em>, and <em>staff</em>, in descending order of privilege. You create an 
initial <em>administrator</em> as part of the application setup by running <span class="text-monospace">bin/admin-cli setup:create-admin-user</span>. 
Then an <em>administrator</em> can create more users by using the application's user management, and each of those users in turn can create further 
users in the same fashion. Think of it like dharma transmission in Zen.

### What are the user’s most common daily activities? What features are used most often?

Again, it depends on the role. The <em>submitter</em>s will spend the overwhelming majority of their time doing CRUD operations with 
Request entities -- in English, creating, viewing, updating and cancelling their requests for interpreters.

On the administrative side, the majority of the users' activity is managing the schedule: viewing, creating, updating, and occasionally 
deleting events. A close second is using the in-context email feature found at <span class="text-monospace">/admin/schedule/view/:id</span>. 
They also make heavy use of the roster of contract interpreters found at  <span class="text-monospace">/admin/interpreters</span>.

For further objective information about what parts of the application are most used, you can: (1) examine the 
web server access logs; (2) take a look at the <span class="text-monospace">app_event_log</span> database table, which provides a fairly rich 
narrative of what people have been up to. For now, the only way to do the latter is with the <span class="text-monospace">mysql</span> cli.

### What are the most common questions users?

It's a bit early to say because there isn't much data yet. The application has been in production since April 25, 2020, and this is 
being written in July 2020. Moreover, court operations have not resumed running at their normal pace thanks to the coronavirus pandemic. 

There have been a few "how do I...?" questions from the Interpreters Office personnel, but in most if not all those cases the answer was found 
somewhere in the navigation links. In other words with a little more exploration they could have figured it out.

Of course, during the first couple of months there have been bug reports and requests for features and modifications. I have been 
responding to these as quickly as possible, and I think it's safe to say everything is sufficiently stable; the application works and users 
are generally comfortable with it. Some have complained about CSRF tokens timing out.

Occasionally users in the <em>submitter</em> role have problems logging in, especially for the initial login following 
account registration. Questions to ask when troubleshooting logins are:  Is the user's account enabled? Has the user ever logged in successfully? 
Is the user's email address correct?

A frequent cause of first-time login problems is that they do not enter their own email address correctly and consequently do not 
receive the email verification message, therefore the account is not enabled. A solution is to manually correct the email address 
and manually enable the account through the user management interface located at <span class="text-monospace">/admin/users</span>.

User accounts can be disabled manually by a sufficiently privileged user, and may be disabled automatically following a 
certain number of failed login attempts as set by [configuration]((/documentation/setup.html#other-configuration)). If a user's account 
has been disabled, whatever the reason, the user will not be able to log in until someone re-enables the account.


### What are the different levels of authorization? What can users do or not do?

In descending order of privilege:

* <span class="text-monospace">administrator</span> can do anything that can be done with the application.
* <span class="text-monospace">manager</span> can do everything except change certain configuration settings and escalate any user's role up to 
<span class="text-monospace">administrator</span>.
* <span class="text-monospace">staff</span> is a rather a somewhat under-privileged role that is intended for temporary people such as interns.
* <span class="text-monospace">submitter</span>s are authorized to do CRUD with their own requests for interpreters. There is an important detail: 
if the user wears a [hat](/documentation/data-model.html#people-and-hats-users-and-roles) that reports directly to a particular judge, then the 
user has write access to any request made on that judges' behalf. All other users have write access only to requests they themselves have created.

Anonymous users can do very little except authenticate. However, please note that by [configuration](/documentation/setup.html#other-configuration) it is 
possible to enable anonymous, read-only access to the schedule if, for example, you're running inside a trusted network. This is not only useful but a 
necessity so that contract interpreters can come on site and see where they are supposed to go.

In broad outline, the authorization scheme is: *submitter*s are authorized to manage their own requests for interpreters and update their user account details 
and nothing else. The others are authorized to manage the interpreters schedule -- the <span class="text-monospace">Event</span> entities -- and related 
entities. The requests are stored separately from the actual events/schedule data for security and administrative reasons. The back-end administrative users 
in the Interpreters Office (*administrator*, *manager*, *staff*) inspect the requests and then copy them to the schedule with a one-click operation. 
This guarantees that they always know what's on their schedule, since they put it their themselves, and it keeps them mindful of staffing needs, e.g., the 
need to reach out to a contract interpreter of a language other than Spanish.

For a complete, detailed understanding of the ACL system you should read the [Laminas documentation](https://docs.laminas.dev/laminas-permissions-acl/) followed by 
[acl configuration file](https://github.com/davidmintz/court-interpreters-office/blob/master/module/Admin/config/acl.php) and the [ACL class](https://github.com/davidmintz/court-interpreters-office/blob/master/module/Admin/src/Service/Acl.php) 
that consumes this configuration.

### Which tool or development environment used to create this application?

Ha! I've had my share of relationships and breakups with IDEs. Right now I am pretty happy, and more or less monogamous, with VS Code.

### How to you push changes to the production server?

I don't exactly push to production servers. I push commits to the github repository, then SSH onto the production servers and 
run <span class="text-monospace">git pull</span>. (With only two machines it isn't too onerous, but some using kind of 
orchestration tool is work considering.) If you fix a bug or make any sort of improvement, please go about it the git/github way. That is,
clone the repository and work on your fork, then submit a pull request. If it's an emergency, of course, then you will just fix the 
source code first and worry about PRs later, but hopefully you will never encounter this scenario.

### Anything else important that we should know about?

Nope. You now know all there is to know :-) 

OK, maybe not. You'll know where to reach me with further questions: david@davidmintz.org


### Is the database and source code being backup daily? If yes, where?

Yes.

First, as for the database, there is a [bash script](https://github.com/davidmintz/court-interpreters-office/blob/master/bin/sdny/db-backup.sh) that runs 
mysqldump and is intended to be invoked by cron. As currently configured in the SDNY, it dumps snapshots multiple times per day 
and rotates out any files older than 7 days; it also does weekly backups which are rotated out every few weeks. The point here is 
that in case something really unfortunate happens and you wish you could roll back to an earlier point and recover some data, you can.

Note that the application interface provides no means of actual physical deletion of rows in either the <span class="text-monospace">events</span> 
  or <span class="text-monospace">requests</span> tables; "delete" is implemented as soft-deletion for these entities.

There is also mysql replication in effect in the SDNY. Within our network we have a primary database server running on one host and a replication server running 
on another. Please see the [Mariadb documentation](https://mariadb.com/kb/en/standard-replication/) for details.

As for the source code, you've got your production installations, and you've got your github. I have sufficient faith in the technical competence of 
github to trust them not to lose our repository. We don't need to worry about this or that instance of the app getting ahead of the github repository 
if we follow the pattern described above: make changes on your development machine, test, commit, push, then go to the production machines and pull.

<hr>
*That's all the Qs we have in our FAQ for now. If you have any questions, especially of 
the end-user variety, please feel free to convey them to me. Thanks,*

*david@davidmintz.org*

