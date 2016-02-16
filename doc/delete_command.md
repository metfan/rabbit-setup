Delete Command
--------------

Rabbit-setup allow you to easily delete `queues` and `exchanges` in RabbitMQ.

```bash
./rabbit-setup.phar rsetup:delete
```
     
This command as many parameters:

 * **username** [default: guest]: allow you to specify username to connect to RabbitMQ
 * **password** [default: guest]: allow you to specify password to connect to RabbitMQ
 * **host** [default: 127.0.0.1]: allow you to specify host of your RabbitMQ instance
 * **port** [default: 15672]: allow you to specify port of your RabbitMQ instance
 * **vhost** (shortcut: -VH) [default: null]: tell which vhost to use to delete queues and/or exchanges
 * **queues** (shortcut: -Q) [default: null]: queues you want to delete, use "*" to delete all queues
 * **exchanges** (shortcut: -E) [default: null]: exchanges you want to delete, use "*" to delete all exchanges
 * **policies** (shortcut: -P) [default: null]: policies you want to delete, use "*" to delete all policies
 
Examples:

You want to delete all policyes, queues and exchanges in your RabbitMQ instance 

```bash
./rabbit-setup.phar rsetup:delete --policies "*" --exchanges "*" --queues "*"
```

You want to delete all queues from a vhost

```bash
./rabbit-setup.phar rsetup:delete --queues "*" --vhost "myvhost"
```

You want to delete one queue

```bash
./rabbit-setup.phar rsetup:delete --queue "myqueue" --vhost "myvhost"
```

You want to delete multiple queue (from same vhost)

```bash
./rabbit-setup.phar rsetup:delete --queue "queue1,queue2,queue3" --vhost "myvhost"
```