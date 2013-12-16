<?php

namespace Francodacosta\CaparicaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Francodacosta\CaparicaBundle\DependencyInjection\Compiler\ClientProviderPass;
use Francodacosta\CaparicaBundle\DependencyInjection\Compiler\KernelControllerListernerPass;

class FrancodacostaCaparicaBundle extends Bundle
{
     public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ClientProviderPass());
        $container->addCompilerPass(new KernelControllerListernerPass());
    }
}
