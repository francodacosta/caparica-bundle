<?php

namespace Francodacosta\CaparicaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FrancodacostaCaparicaExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['client_provider_id'])) {
            $container->setParameter('francodacosta.caparica.client.provider.id', $config['client_provider_id']);
        }

        if (isset($config['timestamp_token'])) {
            $container->setParameter('francodacosta.caparica.token.timestamp', $config['timestamp_token']);
        }

        if (isset($config['signature_token'])) {
            $container->setParameter('francodacosta.caparica.token.signature', $config['signature_token']);
        }

        if (isset($config['client_token'])) {
            $container->setParameter('francodacosta.caparica.token.client', $config['client_token']);
        }

        if (isset($config['kernel_controller_listener'])) {
            $container->setParameter('francodacosta.caparica.listener.kernel.controller.class', $config['kernel_controller_listener']);
        }
    }
}