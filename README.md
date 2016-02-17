Rabbit-setup
------------

[![Build Status](https://travis-ci.org/metfan/rabbit-setup.svg?branch=master)](https://travis-ci.org/metfan/rabbit-setup)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3cae648a-a1c0-4538-b61e-4a8a8fcd564d/mini.png)](https://insight.sensiolabs.com/projects/3cae648a-a1c0-4538-b61e-4a8a8fcd564d)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/metfan/rabbit-setup/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/metfan/rabbit-setup/?branch=master)

Rabbit-setup is a command line tool to ease management of vhosts, exchanges, queues and bindings into RabbitMQ instance via the RabbitMQ's API.
The best way to use rabbit-setup is using phar file.

Use this commande line to compile phar of the project:

    php -d phar.readonly=off ./phar-composer.phar build ./rabbitAdmin/
    
phar-composer can be found here: https://github.com/clue/phar-composer#as-a-phar-recommended

Commands exposed by Rabbit-setup:

- [Expert configuration to create vhost, exchanges, queues](doc/config_expert_command.md)
- [Exchanges, queues deletion](doc/delete_command.md)
- [Validate expert configuration file](doc/validate_expert_command.md)

Thanks to Olivier about inspiration: https://github.com/odolbeau/rabbit-mq-admin-toolkit

TODO:
====

- add support of simple configuration file
- deletion of parameters
- manage users
