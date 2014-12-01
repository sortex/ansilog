# Ansilog
Monitor, management and maintain a history for a centralized Ansible playbook
executions.

## Tech Stack

Name             | Description             | Configuration
---------------- | ----------------------- | --------------------
[Kohana] v3.3.1  | The swift PHP framework |
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
[Ansible]        | Automate IT             | [Playbooks](playbooks/)
[Vagrant]        | Build dev environments  | [Vagrantfile](Vagrantfile)
[VirtualBox]     | Virtual-machine backend | Used by Vagrant

## Automated Install
### Provision Workstation
### Provision Server
### Deployment

## Manual Install
### Virtual Hosts
### Prerequisites
#### Package Managers
### Setup
#### Cron jobs

## Upgrade Process

## License

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
