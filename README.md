# InterpretersOffice	
is a web application for managing busy court interpreters offices (in the English-speaking United States judicial system).

At this writing, the application is far from complete, but I am actively working on it. The intention is to make it available to any court interpreter offices that are interested in adopting it. The motivation is that there is to my knowledge no software available, commercial or otherwise, specifically designed for managing a busy staff court interpreters office. In my own workplace we use an application that I wrote (and which sprang into being precisely because of this dearth of alternatives), and it works great, but needs updating and has too many baked-in features that are peculiar only to our Interpreters Office's needs. So this project will become the world's finest court federal interpreter management system by far (the competition being pretty scarce).

InterpretersOffice is designed primarily with US District Court in mind, but I'm trying to keep it flexible enough for use in state court as well.

# requirements

Requirements are not exotic. In more or less plain English: you are going to need standard hardware and software for serving a web application. That means a computer on a network with its firewall configured to allow web traffic; installed on that computer, a properly configured web server such as [Apache](https://httpd.apache.org/); the [MySQL](https://www.mysql.com/) database server (others will likely work, but we haven't gone there yet); and the programming language [PHP](http://php.net/) version 7.0 or 7.1. On the client side, users will need only a standard web browser and access via the network to the server where the application is installed. If you're running this on your court's intranet, your system administrators should readily understand this stuff. If you opt instead to install it on a commercial web hosting service, that will work as well, because as we said, the underlying software requirements are standard.

The entire software stack -- operating system, web and database servers, application code -- can and should consist of open-source software costing you nothing. If you're running it on your organization's network, an ordinary, inexpensive commodity PC will be plenty powerful enough to support the number of users you're likely to have.

With [docker](https://www.docker.com/) having become such a thing, the chances are very good that I will also publish this application as a docker image. In English, this more or less means a single, self-contained thing that will depend on little else being installed on the computer where it resides.

# installation

At the moment InterpretersOffice isn't really worth installing, since it's still incomplete. But when the time comes, you will need to have installed the industry-standard PHP dependency manager [composer](https://getcomposer.org). Download (or clone) this repository and then run `composer install` from the application root. Then next thing you'll need to do is create your mysql database and a mysql user/password for it. Finally, there will be a couple of configuration files to edit. Details will be in the README files and/or comments in the source files.

# features

There will be an administrative interface with which authenticated users can manage the calendar for their office: view, add, update and delete events involving court interpreters. Events have attributes like date, time, language, judge, docket number, type of proceeding or ancillary event (e.g., attorney-client interview), and of course, the interpreter(s) assigned. You will also  be able to record metadata such as the identity of the person making the request and the date and time it was made.

You will be able to search your database based on all these criteria (date range, docket, judge, language, etc.) and run activity reports.

An optional module will allow users outside your Interpreters Office to log in and submit requests themselves, greatly reducing the amount of data entry required of the Interpreters and eliminating the opportunity for error.

Another optional module I have in mind will generate claim forms for contract interpreter compensation and keep track of the money expended. The work flow currently happening in our organization is preposterously tedious, and this will help immensely.

# acknowledgments

The server-side code is written in PHP and relies heavily on [the Zend MVC Framework](http://framework.zend.com/) 3.x and the [Doctrine Object Relational Mapper](http://www.doctrine-project.org/projects/orm.html). The  front end makes use of [Bootstrap front-end framework](http://getbootstrap.com/), the [jQuery Javascript library](http://jquery.com/), and more.

# the author

My name is [David Mintz](https://davidmintz.org) and I'm a Spanish interpreter on staff at the [Interpreters Office](https://sdnyinterpreters.org/) of the US District Court, Southern District of New York, located in New York City. And I like coding web applications.

# questions? comments?

Please feel free to contact me with any questions or suggestions.


