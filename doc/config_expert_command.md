Expert Configuration Command
----------------------------

Rabbit-setup allow you to create `vhost`, `queues` and `exchanges` in RabbitMQ.

```bash
./rabbit-setup.phar rsetup:config:expert rabbit.yml
```

This command need a very verbose and explicit config file like this:

```yaml
rabbit_setup:
    connections:
        default:
            user: guest
            password: guest
            host: 127.0.0.1
            port: 15672
            
    vhosts:
        "/": #vhost name
            connection: default
            parameters:
                federation-upstream:
                    server1: 
                        uri: "amqp://thor"
                        expires: ~ # in ms
                        message-ttl: ~ # in ms
                        max-hops: ~
                        prefetch-count: ~
                        reconnect-delay: ~
                        ack-mode: ~ #on-confirm|on-publish|no-ack
                        trust-user-id: ~ #true|false
                    server2: { uri: "amqp://192.168.0.99" }
                federation-upstream-set:
                    fedset: [{upstream: server1}, {upstream: server2}]
            policies:
                name:
                    pattern: ~
                    apply_to: ~ #exchanges|queues|all
                    priority: ~
                    definition:
                        ha-mode: ~
                        ha-params: ~
                        ha-sync-mode: ~
                        federation-upstream-set: ~
                        federation-upstream: ~
                        message-ttl: ~ # in ms
                        expires: ~     #in ms
                        max-length: ~
                        max-length-bytes: ~
                        dead-letter-exchange: ~
                        dead-letter-routing-key: ~
                        alternate-exchange: ~
            exchanges:
                ex_test:
                    type: direct    #topic|fanout|direct|headers
                    durable: true
                    auto-delete: false
                    arguments:
                        alternate-exchange: ~
                ex_test_dl:
                    type: direct
                    durable: true
            queues:
                q_test:
                    durable: true
                    auto-delete: false
                    arguments:
                        x-message-ttl: ~ #in ms
                        x-expires: ~     #in ms
                        x-max-length: ~
                        x-max-length-bytes: ~
                        x-dead-letter-exchange: ~
                        x-dead-letter-routing-key: ~
                        x-max-priority: ~
                    bindings:
                        - { exchange: ex_test, routing_key: q_test}
                q_test_ttl300:
                    durable: true
                    auto-delete: false
                    arguments:
                        x-message-ttl: 300
                    bindings:
                        - {exchange: ex_test, routing_key: q_test_ttl300 }
                q_test_dl500:
                    durable: true
                    auto-delete: false
                    arguments:
                        x-message-ttl: 500
                        x-dead-letter-exchange: ex_test_dl
                        x-dead-letter-routing-key: q_test2
                    bindings:
                        - { exchange: ex_test, routing_key: q_test_dl500 }
                q_test2:
                    durable: true
                    auto-delete: false
                    bindings:
                        - { exchange: ex_test_dl, routing_key: q_test2 }
```

The config file map all options availables to create vhosts, exchanges, queues and bindings in RabbitMQ admin control panel.

You can override login and password in the command line using option -u and -p, be carreful this will override login and password for all connections

    ./rabbit-setup.phar apply-config:expert rabbit.yml -u specialUser -p dumbPassword

In RabbitMQ you can't modify configuration of an element. You have to destroy it before. 