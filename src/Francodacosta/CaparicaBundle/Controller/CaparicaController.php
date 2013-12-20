<?php

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