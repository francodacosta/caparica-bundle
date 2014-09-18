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

        if (isset($config['path_token'])) {
            $container->setParameter('francodacosta.caparica.token.path', $config['path_token']);
        }

        if (isset($config['method_token'])) {
            $container->setParameter('francodacosta.caparica.token.method', $config['method_token']);
        }

        if (isset($config['kernel_controller_listener'])) {
            $container->setParameter('francodacosta.caparica.listener.kernel.controller.class', $config['kernel_controller_listener']);
        }

        if (isset($config['validate_timestamp'])) {
            $container->setParameter('francodacosta.caparica.request.validate.timestamp', $config['validate_timestamp']);
        }

        if (isset($config['inclue_path_in_signature'])) {
            $container->setParameter('francodacosta.caparica.signature.includes.path', $config['inclue_path_in_signature']);
        }

        if (isset($config['inclue_method_in_signature'])) {
            $container->setParameter('francodacosta.caparica.signature.includes.method', $config['inclue_method_in_signature']);
        }

        if (isset($config['on_error_redirect_to'])) {
            $container->setParameter('francodacosta.caparica.on.error.redirect.to', $config['on_error_redirect_to']);
        }
    }
}
