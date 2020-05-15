---
layout : default
name : documentation | setup | InterpretersOffice.org

---

# setting up the server

{{ page.project }} is the name.

These are the requirements for running <span class="text-monospace">InterpretersOffice</span>. My intention is make installation easier than what is described below, 
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
This is necessary because <span class="text-monospace">InterpretersOffice</span> uses [an MVC framework](https://docs.laminas.dev/laminas-mvc/) that depends on URL rewriting to route requests to the proper controller/action.

<div class="border border-info rounded sm-shadow py-2 bg-light px-3 mb-3">
A non-root user needing to set up and maintain <span class="text-monospace">InterpretersOffice</span> will need
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
    A non-root user needing to set up and maintain <span class="text-monospace">InterpretersOffice</span> will need
    <ul>
        <li>a mysql user account with permissions to create the database</li>
        <li>permissions to edit the database server configuration files</li>        
        <li>permissions to stop, start, reload the database server</li>
    </ul>
</div>

# <span id="installation">installing the application</span>

### get the code and its dependencies

Install <code>git</code> if you haven't already. This step also requires the
the php dependency manager [composer](https://getcomposer.org). 

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

<span class="text-monospace">InterpretersOffice</span> relies heavily on outbound email. Copy the file 
`config/autoload/local.production.php.dist` to `config/autoload/local.production.php`, and then edit the `mail` section:


```php
<?php

use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;

return [
    // stuff omitted...
    'mail' => [
        'transport' => Smtp::class,
        'transport_options' => [
            'class' => SmtpOptions::class,
            'options' => [
                'name'     => 'your.stmp.server',
                'host'     => 'host_ip_address',
                'port'     => 465,
                'connection_class'  => 'login',

                'connection_config' => [
                    'username' => 'your_username',
                    'password' => 'your_password',
                    'ssl'  => 'ssl',
                ],
            ],
        ],
        'from_address' => 'default_from_address@example.org',
        'from_entity' => 'Interpreters Office',
    ],
];
```
replacing all the values appropriately. This array has to contain configuration that a [Laminas\Mail](https://docs.laminas.dev/laminas-mail/) transport class 
constructor can consume. It does not necessarily have to be the SMTP transport *per se*. If, for example, your system has postfix configured to relay to an 
SMTP server, you can use `Laminas\Mail\Transport\Sendmail` instead:

```php
'mail' => [
        'transport' => 'Laminas\Mail\Transport\Sendmail',
        'transport_options' => [        
            // optional
        ],
]
       
```

Set the values of `from_address` and `from_entity` appropriately; these are the defaults used to populate the `From:` header of outgoing messages.

**If you want to use [Mailgun](https://www.mailgun.com/),** put in the `config/autoload` directory a configuration file called
 `mailgun.local.php` with contents like

```php
return [
    'mailgun' => [
      'smtp' => [
          'user' => 'you@your_email_domain.org',
          'password' => 'your_password',
          'host' => 'smtp.mailgun.org',
      ],
      'api' => [
        'key' => 'your_api_key',        
        'base_url' => 'https://api.mailgun.net/v3',
        'domain'=>  'your_email_domain.org',
      ],      
    ],
];
```

and then Mailgun's SMTP services will be used for sending most emails, but the Mailgun API will be used for the batch email 
feature (which allows administrative users to send email *en masse* to particular groups, such as all active contract interpreters).

### other configuration

There is some more configuration data to be set manually because <span class="text-monospace">InterpretersOffice</span> does 
not yet provide a GUI for all the application configuration.

In `config/autoload/local.production.php` there a couple more sections to be edited.

```php

/** contact information variables for your layout */
'site' => [
    'contact' => [
        'organization_name' => 'Your Office',
        'organization_locality' => 'Your City',
        'telephone' => 'Your phone number',
        'email' => 'contact@example.org',
        'website' => 'https://interpreters.example.org',
    ],        
],
/** 
*   optional list of IP addresses from which users can read the interpreters schedule 
*   without logging in (experimental)  OR pattern that the hosting domain must match
*/
'permissions' => [
    'schedule' => [
        'anonymous_ips_allowed' => [],
        'host_domain_allowed' => '',
    ],
],
'security' => [
    'max_login_failures' => 6,        
],

```

The `contact` array is injected into the main layout and into some email templates. The `permissions` section is currently not 
in use and can be left as is. The `max_login_failures` variable refers to how many consecutive failed logins are permitted 
before a user account is disabled. You can set this value pretty high if you like, but currently you cannot set it to 
zero to mean unlimited. The default (six) seems sensible.


There are a couple more configuration files that have to be present and writeable by the server: `module/Admin/config/forms.json`
`and module/Rotation/config/config.json`. The former looks like


```json
{
    "interpreters": {
        "optional_elements": {
            "banned_by_persons": "1",
            "BOP_form_submission_date": "1",
            "fingerprint_date": "1",
            "contract_expiration_date": "1",
            "oath_date": "1",
            "security_clearance_date": "1"
        }
    },
    "events": {
        "optional_elements": {
            "end_time": "1"
        }
    },
    "users" : {
        "allow_email_domains" : [
            "nysd.uscourts.gov",
            "nysp.uscourts.gov",
            "nyspt.uscourts.gov",
            "ca2.uscourts.gov"    
        ]
    }
}

```

The first two sections are for enabling/disabling optional fields for interpreter entities and event entities.

The `users` section controls what email domains users of the system are permitted to have, preventing users from registering accounts
from arbitrary email addresses. It also imposes validation rules for the user profile and administrative user editing forms. If you 
want to disable this, leave the `allow_email_domains` section as an empty array.

Additionally, the subdirectory `module/Requests/config` has to be server-writeable.


### Hashicorp Vault for sensitive data

<span class="text-monospace">InterpretersOffice</span> includes an optional module called Vault. If you intend to handle sensitive 
contract interpreter data (dates of birth and social security numbers), the Vault module must be configured and enabled. A prerequisite 
is an installation [Hashicorp Vault](https://www.vaultproject.io/) itself. Initial setup of the <span class="text-monospace">InterpretersOffice</span> Vault module 
is a chore, though the increased security is more than worth the effort. We have [some notes here](https://gist.github.com/davidmintz/d1e71331751e6477082c920e01668121) 
but they need updating, as Vault development keeps moving forward.

There is [a blog post about our Vault integration](https://blog.vernontbludgeon.com/integrating-zend-framework-3-with-hashicorp-vault/) with <span class="text-monospace">InterpretersOffice</span> that explains how it works. Briefly, the 
interpreters' sensitive data at rest is symmetrically encryted, and the cipher for encryption/decryption is secured in Vault rather than lying around in plain text. 
In other words the encryption/decryption key is never stored anywhere on the server in plain text.

<div class="border border-info rounded sm-shadow py-2 bg-light px-3 mb-3">
    If you plan to run Vault on the same machine as <span class="text-monospace">InterpretersOffice</span> (although <a href="https://learn.hashicorp.com/vault/operations/production-hardening">Vault recommends single-tenancy</a>), 
    a non-root user 
    needing to set up and maintain <span class="text-monospace">InterpretersOffice</span> will need
    <ul>
        <li>full permissions to manage the Hashicorp Vault service, including its <code>init</code>, <code>seal</code> and <code>unseal</code> commands</li>        
    </ul>
</div>
















