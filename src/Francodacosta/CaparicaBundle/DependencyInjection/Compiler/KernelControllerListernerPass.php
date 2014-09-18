<?php
namespace Francodacosta\CaparicaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class KernelControllerListernerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasParameter('francodacosta.caparica.listener.kernel.controller.class')) {
            throw new InvalidConfigurationException("[Caparica] Missing listener for kernel.controller events");
        }

        $listenerId = $container->getParameter('francodacosta.caparica.listener.kernel.controller.class');

        if (false === $container->has($listenerId)) {
            throw new InvalidConfigurationException("[Caparica] service $listenerId not set");
        }


        $serviceDefinition = $container->getDefinition($listenerId);
        $serviceDefinition->addTag(
            'kernel.event_listener',
            array (
                'event' => 'kernel.controller',
                'method' => 'onKernelController',
            )
        );

        $serviceDefinition->addTag(
            'kernel.event_listener',
            array (
                'event' => 'kernel.exception',
                'method' => 'onKernelException',
            )
        );


        $container->setDefinition($listenerId, $serviceDefinition);
    }
}
