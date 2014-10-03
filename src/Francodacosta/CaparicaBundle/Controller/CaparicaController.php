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
namespace Francodacosta\CaparicaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Book controller.
 *
 */
class CaparicaController extends Controller implements CaparicaControllerInterface
{
    /**
     * holds the client code that identifies the request owner (signer)
     *
     * @var string
     */
    private $clientCode;

    /**
     * Gets the holds the client code that identifies the request owner (signer).
     *
     * @return string
     */
    public function getClientCode()
    {
        return $this->clientCode;
    }

    /**
     * Sets the holds the client code that identifies the request owner (signer).
     *
     * @param string $clientCode the client code
     *
     * @return self
     */
    public function setClientCode($clientCode)
    {
        $this->clientCode = $clientCode;

        return $this;
    }
}
