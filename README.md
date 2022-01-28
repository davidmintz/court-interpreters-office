# InterpretersOffice
is a web application for managing busy court interpreters offices (in the English-speaking United States judicial system).

Developed and used by the Interpreters Office for the US District Court, Southern District of New York, InterpretersOffice
is freely available to any other court interpreter offices that are interested in using it. The motivation for creating it 
is that there is to our knowledge no software available, commercial or otherwise, specifically designed for managing a 
busy staff court interpreters office.

We should also mention, though it may seem obvious, that this an Anglophone-centric project. Court interpreters are required 
in a vast number of language combinations around the world. But this app assumes English as the language of the court.

# features

The administrative interface allows authenticated users to manage the calendar for their office: view, add, update and delete events involving court interpreters. Events have attributes like date, time, place, language, judge, docket number, type of proceeding or ancillary event (e.g., attorney-client interview), and of course, the interpreter(s) assigned. You can also record metadata such as the identity of the person making the request and the date and time it was made.

Among other features:

* You can search your database based on all these criteria (date range, docket, judge, language, etc.) and run activity reports.

* Support for quick and painless emailing of templated assignment details, confirmation and cancellation notices, etc., from within the application -- no need to copy/paste into your email program.

* An optional Requests module which, when enabled,

    * allows users outside your Interpreters Office to log in and manage their own requests for interpreting services, a convenience to them that vastly reduces the amount of data entry required of the Interpreters and eliminates a major source of errors.
    * can be configured to react automatically to certain events, e.g., when a user cancels a scheduled event, the schedule is automatically updated and the interpreter(s) are notified.

* You can create notes relevant to a particular day or week to facilitate administration and intra-office communication, and configure their visibility, size and position to suit your taste -- analogous to a physical post-it note, but tidier and more powerful.

* You can create annotations based on docket numbers, so that you can flag any especially noteworthy aspects of a particular case.

# requirements

If you're considering adopting this software for managing your own interpreters office,
you'll be pleased to know that the requirements, in addition to the application source code
itself, are straightforward. In lay terms: you will need standard
hardware and software for serving a web application. That means a computer on a network with its firewall
configured to allow web traffic; installed on that computer, a properly configured web
server such as [Apache](https://httpd.apache.org/); [MySQL](https://www.mysql.com/) or [MariaDB](https://mariadb.org/) 
as a database server (others will likely work, but we haven't gone there yet); and the programming language
[PHP](http://php.net/), minimum version 7.3. On the client side, users will need no more than a
standard web browser and network access to the server where the application is installed.

If you're planning to run InterpretersOffice on your court's intranet, your system administrators
should readily understand this stuff. If you opt instead to install it on a commercial web hosting
service, that will work as well, because as we said, the underlying software requirements are standard.

With [docker](https://www.docker.com/) having become such a thing, there's a good chance
that we will eventually dockerize this application. In English, this means even less to worry about
in terms of what else has to be installed and set up on the computer where it resides.

The entire software stack -- operating system, web and database servers, application code -- is
built on open-source software and available to you free of charge. If you're running InterpretersOffice
on your organization's network, a single inexpensive commodity PC should be powerful enough to do the job.

All of this is to say that in terms of cost, good news: InterpretersOffice and the supporting software are
completely free; the hardware you'll need is inexpensive.

# installation

Currently, installation requires some manual setup. We plan to make this more convenient in future releases. If you're a technical person 
and want to give it a shot, by all means feel free -- and contact me if you have questions. We have 
some [installation instructions](https://interpretersoffice.org/documentation/setup.html) at the project website, 
[interpretersoffice.org](https://interpretersoffice.org).

Essentially, you need clone the repository (https://github.com/davidmintz/court-interpreters-office.git) or 
[download the source code](https://github.com/davidmintz/court-interpreters-office/releases/latest); using the industry-standard 
PHP dependency manager [composer](https://getcomposer.org), install the the software dependencies; create your mysql database and a mysql user/password 
for the application to use; initialize the database with a supplied SQL script; and finally, there are a couple of configuration files to edit and 
directories to be created and made server-writeable.

# known issues and limitations

The SDNY Interpreters Office has been successfully running the current major version of`InterpretersOffice` since April 2020; predecessor versions have been 
in use since 2000. It is stable and production-ready. 
There are still a few ragged edges which, once smoothed out, should improve long-term maintainability and performance. The following 
are for the most part invisible to end users:
* The JQuery-based Javascript is old-school and though it works fine, it should be migrated to [webpack](https://webpack.js.org/).  
* The ways in which similar problems are handled in various parts of the application are not always consistent, as I was 
learning more about Doctrine ORM as the project progressed.
*  `InterpretersOffice` has been designed with the US federal court system in mind. It is not currently well-suited to other 
judiciary systems, although we could and would change this in the future -- especially if there is interest.
* The logic involved in email templating and the implementation of the Notes (MOTD/MOTW) module is needlessly complicated.
* The web application framework that we use -- [Laminas](https://getlaminas.org/), formerly known as Zend -- is not as popular, well-known, and easy to use as its competitors. If I had it to do over, I would prefer [Symfony](https://symfony.com/).


# demonstration

Yes! A demonstration site is available. Please contact me for details.

# questions? comments?

Feel free to contact me with any questions or suggestions: [david@davidmintz.org](mailto:david@davidmintz.org)

# the author

My name is [David Mintz](https://davidmintz.org) and until my retirement in July 2020 I was a Spanish interpreter on staff at the [Interpreters Office](https://sdnyinterpreters.org/) of the US District Court, Southern District of New York, located in New York City. My career as a court interpreter lasted over 30 years. As a staff interpreter I was motivated to pick up web application development by the complete lack of software for managing an interpreters office.

# acknowledgments

Our server-side code is written in [PHP](https://php.net) and relies heavily on the  [Laminas MVC Framework](https://docs.laminas.dev/) (formerly Zend) and the [Doctrine Object Relational Mapper](http://www.doctrine-project.org/projects/orm.html). The  front end makes use of [Bootstrap front-end framework](http://getbootstrap.com/), the [jQuery Javascript library](http://jquery.com/), and more. We are boundlessly grateful to the people who make these superb tools available.
