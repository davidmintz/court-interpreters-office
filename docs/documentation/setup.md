---
layout : default
name : setup
---

# setting up the application server

These are the requirements for running InterpretersOffice.

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

1. Install <code>php7.4-fpm</code>
2. Install php extensions: <code>php7.4&#8209;mysql</code>, <code>php7.4&#8209;curl</code>, <code>php7.4&#8209;mbstring</code>, <code>php7.4&#8209;json</code>, <code>php7.4&#8209;dom</code>, <code>php7.4&#8209;intl</code>
3. Install php [composer](https://getcomposer.org)

###  <span id="database">database server: *mariadb or mysql*</span>
<ol>
    <li>Install the database server<br>
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
    A non-root user needing to set up and maintain InterpretersOffice <em>may</em> need
    <ul>
        <li>sufficient permissions to stop, start, reload the database server</li>
        <li>sufficient permissions to edit the database server configuration files</li>        
    </ul>
</div>

### <span id="installation">install the application</span>
Install <code>git</code> if you haven't already. Then
```
cd /path/to/application/
git clone https://github.com/davidmintz/court-interpreters-office
cd court-interpreters-office
composer install
```













