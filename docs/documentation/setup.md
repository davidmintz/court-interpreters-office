---
layout : default
name : setup | InterpretersOffice.org
---

# setting up the server

These are the requirements for running InterpretersOffice. My intention is make installation easier than what is described below, 
with less manual setup involved, but for now <em>illud est quod est</em>. And of course, if you already have your web and database servers 
installed, there is less to do. These instructions assume a server with a Linux OS and little else.

### <span id="web-server">web server: *Apache or Nginx*</span>

Since I'm personally more familiar with Apache, we'll assume the use of Apache.

1. Install the web server.
2. Set up the virtual host.  
* Decide whether you want to allow per-directory overrides (with a <code>.htaccess</code> file) and put the [url-rewriting recipe](https://github.com/davidmintz/court-interpreters-office/blob/master/public/.htaccess) there, or 
else put it in the vhost configuration and (optionally) disable overrides. The latter strategy gives you marginally better performance, at the cost of having to 
reload the server should you need to change this configuration.
* Set as web document root the full path to <code>public</code> subdirectory under the application root.
* Set an environment variable called _environment_ to: **production**.
3. Enable <code>mod_rewrite</code> (or equivalent)  
This is necessary because InterpretersOffice uses [an MVC framework](https://docs.laminas.dev/laminas-mvc/) that depends on URL rewriting to route requests to the proper controller/action.

<div class="border border-info rounded sm-shadow py-2 bg-light px-3 mb-3">
A non-root user needing to set up and maintain InterpretersOffice will need
<ul>
    <li>sufficient permissions to stop, start, reload the web server</li>
    <li>sufficient permissions to edit the web server configuration files</li>
    <li>ownership of the entire application directory tree, or at least read/write access to it</li>
</ul>
</div>

### <span id="php-support">php support</span>

1. Install <code>php7.4-fpm</code>. (The minimum supported version is 7.2)
2. Install php extensions: <code>php7.4&#8209;mysql</code>, <code>php7.4&#8209;curl</code>, <code>php7.4&#8209;mbstring</code>, <code>php7.4&#8209;json</code>, <code>php7.4&#8209;dom</code>, <code>php7.4&#8209;intl</code>
3. Install php [composer](https://getcomposer.org)

###  <span id="database">database server: *mariadb or mysql*</span>
<ol>
    <li>Install the database server.<br>
        You can use either mysql or mariadb but for master-server replication it is best to pick one or the other exclusively. I have been happy with 
both but currently my preference is mariadb. 
    </li>
    <li>Create the database.<br>You can call it what you like, but the name I have been using is <strong>office</strong>.</li>
    <li>Create the database user and grant privileges:<br>  
        <code style="color:black">
            CREATE USER 'interpreters'@'localhost' IDENTIFIED BY 'their_password';<br>
            GRANT ALL PRIVILEGES ON `office`.* TO 'interpreters'@'localhost';
        </code>
    </li>
</ol>
 <div class="border border-info rounded sm-shadow py-2 bg-light px-3 mb-3">
    A non-root user needing to set up and maintain InterpretersOffice will need
    <ul>
        <li>a mysql user account with permissions to create the database</li>
        <li>permissions to edit the database server configuration files</li>        
        <li>permissions to stop, start, reload the database server</li>
    </ul>
</div>

# <span id="installation">installing the application</span>

### get the code and its dependencies

Install <code>git</code> if you haven't already. Then
```
cd /path/to/application/
git clone https://github.com/davidmintz/court-interpreters-office
cd court-interpreters-office
composer install
```
You can safely ignore any warning about *package container-interop/container-interop* having been abandoned. Moving on:

### set up the data directory
```
mkdir -p data/log
mkdir data/session
mkdir data/cache
```
Make these writeable by the web server, e.g.,
```
setfacl -Rm u:www-data:rwx data
```

### set up the database
Create and edit the database configuration file:

```
cd config/autoload
cp doctrine.local.php.dist doctrine.local.php
```

Open <code>doctrine.local.php</code> in a text editor, set the values for *user*, *password*, and *host* appropriately, and save.

You should now be able to load the index page in a browser without incident, but there is still more to do, starting 
with database initialization.

**If you are migrating from an existing installation**, simply <code>mysqldump</code> the old database into the new one, and your database is good to go. (After first 
shutting off access the old installation, to avoid losing data in the transition.) 

**If you are starting from scratch** with an empty database, you need to seed the database by running SQL scripts found in <code>bin/sql</code>. From 
your application root,

```
cat bin/sql/mysql-schema.sql bin/sql/initial-data.sql | mysql -p  -h your_host office
```

Next, you need to set up an initial administrative user, using the provided interactive CLI script. From the application root,
```
bin/admin-cli setup:create-admin-user
```
Supply the answers as prompted, and you'll have an admin user who can log in and carry on using the web interface. 
*(Please note: at this point it is technically possible to move forward with the database as is, but there are several data 
tables that would have to be populated by the admin users, row by row, and it would be tedious. It's worth considering writing 
some one-off scripts to import data from existing sources. Feel free to contact david@davidmintz.org to discuss.)*

### email

To be continued...

### final configuration

In the <code>config/autoload</code> folder,


Likewise to be continued.














