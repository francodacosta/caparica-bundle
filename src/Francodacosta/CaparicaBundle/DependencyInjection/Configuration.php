<?php

namespace Francodacosta\CaparicaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('francodacosta_caparica');

         $rootNode
            ->children()

                ->scalarNode('client_provider_id')
                    ->defaultValue('francodacosta.caparica.client.provider.yaml')
                    ->cannotBeEmpty()
                    ->info("sets the id of the client provider service")
                ->end()

                ->scalarNode('timestamp_token')
                    ->cannotBeEmpty()
                    ->defaultValue('X-CAPARICA-TIMESTAMP')
                    ->info("the name of the token holding the request timestamp")
                ->end()

                ->scalarNode('signature_token')
                    ->cannotBeEmpty()
                    ->defaultValue('X-CAPARICA-SIG')
                    ->info("the name of the token holding the request signature")
                ->end()

                ->scalarNode('client_token')
                    ->cannotBeEmpty()
                    ->defaultValue('X-CAPARICA-CLIENT')
                    ->info("the name of the token holding the client code")
                ->end()

                ->scalarNode('kernel_controller_listener')
                    ->cannotBeEmpty()
                    ->defaultValue('francodacosta.caparica.listener.kernel.controller')
                    ->info("the service id of the kernel controller event listener, this class will be automatically registered as a listner")
                ->end()

                ->booleanNode('validate_timestamp')
                    ->defaultTrue()
                    ->info("should the request timestamp be validaded, this helps to prevent replay attacks")
                ->end()

            ->end();

        return $treeBuilder;
    }
}
