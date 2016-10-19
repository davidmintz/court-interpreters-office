# InterpretersOffice	
is a web application for managing busy court interpreters offices (in the English-speaking United States judicial system).

At this writing, the application is far from complete and not yet useable. I am actively working on it. The intention is to make it available to any court interpreter offices that are interested in adopting it. The motivation is that there is no suitable software available, to my knowledge, for managing a busy staff court interpreters office. In my own workplace we use an application that I wrote, and it works well, but it needs updating and has too many baked-in features that are peculiar only to our needs. So this one will become the world's finest by far (the competition being pretty scarce).

InterpretersOffice is designed primarily with US District Court in mind, but I'm trying to keep it flexible enough for use in state court as well.

# requirements

Requirements are not exotic. In more or less plain English: you are going to need standard hardware and software for serving a web application. That means a computer on a network with its firewall configured to allow web traffic; installed on that computer, a properly configured web server such as [Apache](https://httpd.apache.org/); the [MySQL](https://www.mysql.com/) database server (others will likely work, but we haven't gone there yet); and the programming language [PHP](http://php.net/) 7.0 (5.6 and above should work). On the client side, users will need only a standard web browser and access via the network to the server where the application is installed. If you're running this on your court's intranet, your system administrators should readily understand this stuff. If you opt instead to run it on a commercial web hosting service, that will work as well, because as we said, the underlying software requirements are standard.

You can (and should) run this entirely on open-source software costing you nothing. If you're running it on your organization's network, an ordinary, inexpensive commodity PC will be plenty powerful enough to support the number of users you're likely to have.

# installation

At the moment InterpretersOffice isn't really worth installing, since it doesn't do very much as of yet. But when the time comes, you will need to have installed the industry-standard PHP dependency manager [composer](https://getcomposer.org). Download (or clone) this repository and then run `composer install` from the application root. Then next thing you'll need 
to do is create your mysql database and a mysql user/password for it. Finally, there will be a couple of configuration files 
to edit. Details will be in the README files and/or comments in the source files.

# features

There will be an administrative interface with which authenticated users can manage the calendar for their office: view, add,
update and delete events involving court interpreters. These "events" have attributes like date, time, language, judge,
docket number, type of proceeding or ancillary event (such as an attorney-client interview), and of course, the interpreter(s) assigned. You will also  be able to record metadata such as the identity of the person making the request and the date and time it was made.

You will be able to search your database based on all these criteria (date range, docket, judge, language, etc.) and run activity reports.

An optional module will allow users outside your Interpreters Office to log in and submit requests themselves, greatly reducing the amount of data entry required of the Interpreters and eliminating the opportunity for error.

# acknowledgments

The server-side code is written in PHP and relies heavily on [the Zend MVC Framework](http://framework.zend.com/) 3.x and the [Doctrine Object Relational Mapper](http://www.doctrine-project.org/projects/orm.html). The  front end will make use of [Bootstrap front-end framework](http://getbootstrap.com/), the [jQuery Javascript library](http://jquery.com/), and more.

# the author

My name is [David Mintz](https://davidmintz.org) and I'm a Spanish interpreter on staff at the [Interpreters Office](https://sdnyinterpreters.org/) of the US District Court, Southern District of New York, located in New York City. And I like coding web applications.

Feel free to contact me with any questions or suggestions.


