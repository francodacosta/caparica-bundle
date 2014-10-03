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
namespace Francodacosta\CaparicaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ClientProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasParameter('francodacosta.caparica.client.provider.id')) {
            throw new InvalidConfigurationException("[Caparica] Missing client provider id");
        }

        $providerId = $container->getParameter('francodacosta.caparica.client.provider.id');
        $container->addAliases(['francodacosta.caparica.client.provider' => $providerId]);
    }
}
