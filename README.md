Ansilog
---
Monitor, management and maintain a history for a centralized Ansible playbook
executions.

Tech Stack
---

Name             | Description             | Configuration
---------------- | ----------------------- | --------------------
[Kohana] v3.4.x  | The swift PHP framework |
[Mustache]       | Logic-less templates    |
[Minion]         | Command-line PHP tasks  |
[Backbone.js]    | JS framework            |
[Marionette.js]  | Backbone.js extension   |
[RequireJS]      | AMD modules             | [config.js](src/core/media/js/config.js), [build.js](build.js)
[PHPSpec]        | BDD tests               | [phpspec.yml](phpspec.yml)
[Composer]       | PHP dependency manager  | [composer.json](composer.json)
[NPM]            | Node package manager    | [package.json](package.json)
[Bower]          | Package manager         | [bower.json](bower.json), [.bowerrc](.bowerrc)
[Grunt]          | JS task runner          | [Gruntfile.js](Gruntfile.js)
[Git Submodules] | 3rd-party repositories  | [.gitmodules](.gitmodules)
[Ansible]        | Automate IT             | [Playbooks](boot/playbooks/)
[Vagrant]        | Build dev environments  | [Vagrantfile](boot/Vagrantfile)
[VirtualBox]     | Virtual-machine backend | Used by Vagrant

Setup & Install
---

## Getting Started
1. Clone project:
  `git clone git@github.com:sortex/ansilog.git && cd ansilog`
2. Update git submodules: `git submodule update --init`
3. [Install Ansible](http://docs.ansible.com/intro_installation.html#latest-releases-via-apt-ubuntu)

### Provision Workstation
1. Install Vagrant and NFS (Ubuntu): `apt-get install vagrant nfs-kernel-server`
2. Create your vagrant instance: `vagrant up`
3. Add following line to your `/etc/hosts`: `33.33.33.10  ansilog.vm`

### Provision Server
### Deployment

## Manual Install
If you choose not to use Vagrant and want to manually prepare your workstation,
first install the required package managers:
- Node.js, [NPM]
- [Composer]
- [Bower] and [Grunt]: `npm install -g bower grunt-cli`

Then run:
```sh
npm install
composer install
bower install
grunt
```

### Virtual Hosts
Apache development vhost conf example:
```
<VirtualHost *:80>

	ServerAdmin webmaster@localhost
	ServerName ansilog.vm
	DocumentRoot /srv/http/ansilog/srv/http

	RewriteEngine on

	# Short-circuit for 'common' subpath
	RewriteRule ^/common/(.*)$ /srv/http/ansilog/srv/assets/common/$1 [L]

	# Unless file/folder exists, execute index.php
	RewriteCond /srv/http/ansilog/srv/http/%{REQUEST_FILENAME} !-f
	RewriteCond /srv/http/ansilog/srv/http/%{REQUEST_FILENAME} !-d
	RewriteRule .* /srv/http/ansilog/srv/http/index.php [L]

	# Allow any files or directories that exist to be displayed directly
	RewriteRule .* /srv/http/ansilog/srv/http/$0

	# Protect hidden files from being viewed
	<Files .*>
		Order Deny,Allow
		Deny From All
	</Files>

	<Directory /srv/http/ansilog/>
		Options -Indexes +FollowSymLinks -MultiViews
		AllowOverride All
		Require all granted
	</Directory>

	# Levels: debug, info, notice, warn, error, crit, alert, emerg
	LogLevel warn

	ErrorLog /var/log/httpd/ansilog_error.log
	CustomLog /var/log/httpd/ansilog_access.log combined

</VirtualHost>
```

### Cron jobs

Upgrade
---
After an upgrade, you need to update git submodules, and compile stylesheets.
As a developer, you're expected to upgrade after every pull/merge.
- Updating submodules: `git submodule update --init --recursive`
- Compiling assets in development: `grunt`

Architecture
---

## Kohana-based Extensions
- Using only composer's autoloader, [example](srv/http/index.php)
- Using CFS/`Kohana::find_file` and `Kohana::modules` only for static/media/views
- [Config](src/Kohana/classes/Config.php): Pre-load files
- [Config Reader](src/Kohana/classes/Config/Ini.php): Ini-based with nested
    groups, and **does not** use CFS/`Kohana::find_file`
- [Internal Request Client](src/Kohana/classes/Request/Client/Internal.php): Support namespaces
- [Base Controller](src/Kohana/classes/Controller.php): Automatic DI resolver
- [Content Controllers](src/Kohana/classes/Controller): HTML, Static media, REST
- [Kohana Exception](src/Kohana/classes/Kohana/Exception.php): Outputs PHP views by HTTP quality type
- [Loggers](src/Kohana/classes/Log): Email, File (group by year/month/day), Hipchat, Sentry
- [Minion](src/Kohana/classes/Minion): Overwriting original classes to support PostgreSQL
- [Kohana Mustache Loaders](src/Kohana/classes/Mustache): Loader (uses CFS/`Kohana::find_file`), Alias Loader

## App
- Compose controllers with abilities, see [example](app/classes/Controller/Site/Page.php)
- Integrate tools, [example](app/classes/Tool/Profiler.php)
- Mustache.php with template inheritance, [example](app/media/templates/site)

## IT Orchestration
- Packer to build Archlinux ISO, [boot/packer](boot/packer)
- Vagrant configuration, [boot/Vagrantfile](boot/Vagrantfile)
- Ansible plays for provisioning and deployment, [boot/playbooks](boot/playbooks)

## Configuration
INI files, php.ini-style with support for **nested groups**.
Formation on loading performs a deep-merge of following files in-order:
- [`etc/environments/all.ini`](etc/environments/all.ini)
- [`etc/environments/<ENV_NAME>.ini`](etc/environments)
- [`app/app.ini`](app/app.ini)

## Content Delivery
- `http`, Routes formation:
  - Global routes, [srv/http/index.php](srv/http/index.php#L83)
  - Application routes, [app/routes.php](app/routes.php)
- `cli`, [Minion] tasks, [srv/cli/index.php](srv/cli/index.php)
- `assets`, [srv/assets](srv/assets)

License
---
Copyright (c) 2014-2015, Sortex
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.



[Kohana]: http://kohanaframework.org/3.3/guide/
[Mustache]: https://github.com/bobthecow/mustache.php
[Minion]: https://github.com/kohana/minion
[PHPSpec]: http://www.phpspec.net/
[Composer]: https://getcomposer.org/
[NPM]: https://www.npmjs.org/
[Bower]: http://bower.io/
[Grunt]: http://gruntjs.com/
[Git Submodules]: http://git-scm.com/book/en/Git-Tools-Submodules
[RequireJS]: http://requirejs.org/
[Backbone.js]: http://backbonejs.org/
[Marionette.js]: http://marionettejs.com/
[Ansible]: http://www.ansible.com/
[Vagrant]: http://www.vagrantup.com/
[VirtualBox]: https://www.virtualbox.org/
