<?php
namespace Metfan\RabbitSetup\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;


/**
 * Declaration of configuration to validate and normalize config file
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Configuration
 */
class ConfigExpertConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('rabbit_setup');

        $rootNode
            ->validate()
                ->always()
                ->then(function($v){
                    /**
                     * ensure that connections are correctly declared between vhosts and connection
                     */
                    foreach ($v['vhosts'] as $name => $config) {
                        if (!isset($v['connections'][$config['connection']])) {
                            throw new InvalidTypeException(sprintf(
                                'Connection name "%s" for vhost %s have to be declared in "connections" section.',
                                $config['connection'],
                                $name
                            ));
                        }
                    }

                    return $v;
                })
            ->end()
            ->children()
                ->arrayNode('connections') #definition of connections
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name') #use name as identifier
                    ->prototype('array')
                        ->children()
                            ->scalarNode('user')->defaultValue('guest')->end()
                            ->scalarNode('password')->defaultValue('guest')->end()
                            ->scalarNode('host')->defaultValue('127.0.0.1')->end()
                            ->scalarNode('port')->defaultValue(15672)->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('vhosts') # definition of vhosts
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->validate()
                            ->ifTrue(function($v){ return count($v['queues']) > 0;})
                            ->then(function($v) {
                                foreach ($v['queues'] as $name => $config) {
                                    if (isset($config['bindings'])) {
                                        foreach ($config['bindings'] as $binding) {
                                            if (!isset($v['exchanges'][$binding['exchange']])) {
                                                throw new InvalidTypeException(sprintf(
                                                    'Exchange "%s" use in binding of queue %s must be declared under exchanges key configuration.',
                                                    $binding['exchange'],
                                                    $name
                                                ));
                                            }
                                        }
                                    }

                                    if (isset($config['arguments'])
                                        && isset($config['arguments']['x-dead-letter-exchange'])
                                        && !isset($v['exchanges'][$config['arguments']['x-dead-letter-exchange']])) {
                                        throw new InvalidTypeException(sprintf(
                                            'Exchange "%s" use in x-dead-letter-exchange of queue %s must be declared under exchanges key configuration.',
                                            $config['arguments']['x-dead-letter-exchange'],
                                            $name
                                        ));
                                    }
                                }

                                return $v;
                            })
                        ->end()
                        ->children()
                            ->scalarNode('connection')->isRequired()->cannotBeEmpty()->end() #connection to use
                            ->append($this->addParameters()) #add parameters definition
                            ->append($this->addPolicies()) #add policies definition
                            ->append($this->addExchanges()) #add exchanges definition
                            ->append($this->addQueues()) #add queues definition
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    public function addParameters()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('parameters');

        $node
            ->validate()
                ->ifTrue(function($v) {return isset($v['federation-upstream-set']);})
                ->then(function($v){
                    /**
                     * Verify all server name use in federation-upstream-set
                     * were declared in federation-upstream
                     */
                    foreach ($v['federation-upstream-set'] as $name => $federationSet) {
                        foreach ($federationSet as $config) {
                            if ('all' != $config['upstream']
                                && !array_key_exists($config['upstream'], $v['federation-upstream'])) {
                                throw new InvalidTypeException(sprintf(
                                    'Unknown upstream server name: "%s" in upstream set: "%s".',
                                    $config['upstream'],
                                    $name));
                            }
                        }
                    }
                    return $v;
                })
            ->end()


            ->normalizeKeys(false) # don't transforme - into _
            ->children()
                ->arrayNode('federation-upstream')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->normalizeKeys(false) # don't transforme - into _
                        ->children()
                            ->scalarNode('uri')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('expires')
                                ->validate()
                                ->always()
                                    ->then(function($value){
                                        if (!$value) {
                                            return null;
                                        } elseif (is_int($value)){
                                            return (int) $value;
                                        }

                                        throw new InvalidTypeException(sprintf(
                                            'Invalid value for path "expires". Expected integer or null, but got %s.',
                                            gettype($value)));
                                    })
                                ->end()
                                ->info('# in ms, leave blank mean forever')
                                ->defaultValue(null)
                            ->end()
                            ->scalarNode('message-ttl')
                                ->validate()
                                ->always()
                                    ->then(function($value){
                                        if (!$value) {
                                            return null;
                                        } elseif (is_int($value)){
                                            return (int) $value;
                                        }

                                        throw new InvalidTypeException(sprintf(
                                            'Invalid value for path "expires". Expected integer or null, but got %s.',
                                            gettype($value)));
                                    })
                                ->end()
                                ->info('# in ms, leave blank mean forever')
                                ->defaultValue(null)
                            ->end()
                            ->integerNode('max-hops')
                                ->min(1)
                                ->defaultValue(1)
                            ->end()
                            ->integerNode('prefetch-count')
                                ->min(0)
                                ->defaultValue(1000)
                            ->end()
                            ->integerNode('reconnect-delay')
                                ->min(0)
                                ->info('# in s')
                                ->defaultValue(5)
                            ->end()
                            ->enumNode('ack-mode')
                                ->values(['on-confirm', 'on-publish', 'no-ack'])
                                ->defaultValue('on-confirm')
                            ->end()
                            ->booleanNode('trust-user-id')
                                ->treatNullLike(false)
                                ->defaultValue(false)
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('federation-upstream-set')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('upstream')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    public function addPolicies()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('policies');

        $node
            ->validate()
                ->always()
                ->then(function($v) {
                    /**
                     * When applying policy to queues or exchanges some options can't be use
                     * depending of apply type.
                     */
                    foreach ($v as $policyName => $policy) {
                        $definitionsUsed = array_keys($policy['definition']);
                        $intersect = [];
                        if ('exchanges' == $policy['apply-to']) {
                            $forbid = [
                                'message-ttl',
                                'expires',
                                'max-length',
                                'max-length-bytes',
                                'dead-letter-exchange',
                                'dead-letter-routing-key'];
                            $intersect = array_intersect($definitionsUsed, $forbid);
                        } elseif ('queues' == $policy['apply-to']) {
                            $intersect = array_intersect($definitionsUsed, ['alternate-exchange']);
                        }

                        if (count($intersect) > 0) {
                            throw  new InvalidTypeException(sprintf(
                                'You can\'t use "%s" with %s in policy: %s',
                                implode(', ', $intersect),
                                $policy['apply-to'],
                                $policyName
                            ));
                        }
                    }

                    return $v;
                })
            ->end()

            ->useAttributeAsKey('name')
            ->prototype('array')
                ->normalizeKeys(false) # don't transforme - into _
                ->children()
                    ->scalarNode('pattern')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->integerNode('priority')
                        ->defaultValue(0)
                    ->end()
                    ->enumNode('apply-to')
                        ->values(['exchanges', 'queues', 'all'])
                        ->defaultValue('all')
                    ->end()
                    ->arrayNode('definition')
                        ->isRequired()
                        ->normalizeKeys(false) # don't transforme - into _
                        ->children()
                            ->integerNode('message-ttl')->end()
                            ->integerNode('expires')->end()
                            ->integerNode('max-length')->end()
                            ->integerNode('max-length-bytes')->end()
                            ->scalarNode('alternate-exchange')->end()
                            ->scalarNode('dead-letter-exchange')->end()
                            ->scalarNode('dead-letter-routing-key')->end()
                            ->scalarNode('federation-upstream-set')->end()
                            ->scalarNode('federation-upstream')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    public function addExchanges()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('exchanges');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->normalizeKeys(false) # don't transforme - into _
                ->children()
                    ->enumNode('type')
                        ->values(['topic', 'fanout', 'direct', 'headers'])
                        ->isRequired()
                    ->end()
                    ->enumNode('durability')
                        ->values(['durable', 'transiant'])
                        ->defaultValue('durable')
                    ->end()
                    ->booleanNode('auto-delete')
                        ->defaultValue(false)
                    ->end()
                    ->booleanNode('internal')
                        ->defaultValue(false)
                    ->end()
                    ->scalarNode('alternate-exchange')
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    public function addQueues()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('queues');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->normalizeKeys(false) # don't transforme - into _
                ->children()
                    ->enumNode('durability')
                        ->values(['durable', 'transiant'])
                        ->defaultValue('durable')
                    ->end()
                    ->booleanNode('auto-delete')
                        ->defaultValue(false)
                    ->end()
                    ->arrayNode('arguments')
                        ->normalizeKeys(false) # don't transforme - into _
                        ->children()
                            ->integerNode('x-message-ttl')->end()
                            ->integerNode('x-expires')->end()
                            ->integerNode('x-max-length')->end()
                            ->integerNode('x-max-length-bytes')->end()
                            ->integerNode('x-max-priority')->end()
                            ->scalarNode('x-dead-letter-exchange')->end()
                            ->scalarNode('x-dead-letter-routing-key')->end()
                        ->end()
                    ->end()
                    ->arrayNode('bindings')
                        ->prototype('array')
                                ->children()
                                    ->scalarNode('exchange')
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('routing_key')
                                        ->isRequired()
                                    ->end()
                                ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
