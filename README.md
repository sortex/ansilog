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

## Getting started
1. Clone project to your workstation and get into it:
  `git clone git@github.com:sortex/ansilog.git && cd ansilog`
2. Update submodules: `git submodule update --init`
3. [Install Ansible](http://docs.ansible.com/intro_installation.html#latest-releases-via-apt-ubuntu)
3. Install Vagrant and NFS, e.g. Ubuntu:
  `sudo apt-get install vagrant nfs-kernel-server`
5. Create your vagrant instance: `vagrant up`

## Automated Install
First, [install](http://docs.ansible.com/intro_installation.html) Ansible on
your central machine (usually local computer). This is the machine you'll use to
run the playbooks on remote servers.

### Provision Workstation
Use [Vagrant] and [Ansible] to create a virtual-machine with
all dependencies and software installed:

	vagrant up

### Provision Server
### Deployment

## Manual Install
### Virtual Hosts
### Prerequisites
- nginx or Apache
- PHP 5.5.x
- Node.js
- Composer, NPM, grunt, bower
- Git

#### Package Managers
Install package managers:
- Composer: `curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer`
- Bower and Grunt: `npm install -g bower grunt-cli`

### Setup
```sh
npm install
composer install
bower install
grunt
```

#### Cron jobs

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
- `spec`, [srv/spec](srv/spec)

License
---
:question:

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
