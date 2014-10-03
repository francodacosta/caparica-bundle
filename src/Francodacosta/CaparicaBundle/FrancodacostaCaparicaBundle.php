<?php
/**
 * Caparica Symfony Bundle
 *
 * Signed requests
 *
 * @author    Nuno Franco da Costa <nuno@francodacosta.com>
 * @copyright 2013-2014 Nuno Franco da Costa
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/francodacosta/caparica
 */
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
