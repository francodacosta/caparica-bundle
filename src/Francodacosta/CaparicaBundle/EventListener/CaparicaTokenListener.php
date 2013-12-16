<?php

namespace Francodacosta\CaparicaBundle\EventListener;

use Francodacosta\CaparicaBundle\Controller\CaparicaControllerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Caparica\Security\RequestValidatorInterface;

class CaparicaTokenListener
{
    private $caparicaRequestValidator;

    private $tokenKey;
    private $clientKey;
    private $timestampKey;

    public function __construct(RequestValidatorInterface $caparicaRequestValidator)
    {
        $this->caparicaRequestValidator = $caparicaRequestValidator;
    }


    /**
     * because token and timestamp can be set via headers, we remove them from the parameters array
     *
     * @param  array  $params
     *
     * @return array
     */
    private function prepareParametersArray(array $params)
    {
        unset($params[$this->tokenKey]);
        unset($params[$this->timestampKey]);

        return $params;
    }

    private function getValue($request, $key)
    {
        $value = $request->get($key);

        if (null == $value) {
            $value = $request->headers->get($key);
        }

        return $value;
    }
    private function validate(FilterControllerEvent $event)
    {
        $caparicaRequestValidator = $this->caparicaRequestValidator;
        $request = $event->getRequest();

        $params = $this->prepareParametersArray((array) $request->query->getIterator());

        $params[$this->timestampKey] = $this->getValue($request, $this->timestampKey);
        if (null == $params[$this->timestampKey] ) {
            throw new \InvalidArgumentException("Missing timestamp", 400);

        }

        $clientId = $this->getValue($request, $this->clientKey);
        if (null == $clientId) {
            throw new \InvalidArgumentException("Missing client code", 400);

        }

        $token = $this->getValue($request, $this->tokenKey);
        if (null == $token) {
            throw new \InvalidArgumentException("Missing token", 400);
        }

        return $caparicaRequestValidator->validate($clientId, $token, $params);
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        /*
         * $controller passed can be either a class or a Closure. This is not usual in Symfony2 but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof CaparicaControllerInterface) {
            if (false === $this->validate($event)) {
                throw new \Exception('Your signature does not match server computed signature', 401);

            }
        }

    }

    /**
     * Gets the value of caparicaRequestValidator.
     *
     * @return mixed
     */
    public function getCaparicaRequestValidator()
    {
        return $this->caparicaRequestValidator;
    }

    /**
     * Sets the value of caparicaRequestValidator.
     *
     * @param mixed $caparicaRequestValidator the caparica request validator
     *
     * @return self
     */
    public function setCaparicaRequestValidator($caparicaRequestValidator)
    {
        $this->caparicaRequestValidator = $caparicaRequestValidator;

        return $this;
    }

    /**
     * Gets the value of tokenKey.
     *
     * @return mixed
     */
    public function getTokenKey()
    {
        return $this->tokenKey;
    }

    /**
     * Sets the value of tokenKey.
     *
     * @param mixed $tokenKey the token key
     *
     * @return self
     */
    public function setTokenKey($tokenKey)
    {
        $this->tokenKey = $tokenKey;

        return $this;
    }

    /**
     * Gets the value of clientKey.
     *
     * @return mixed
     */
    public function getClientKey()
    {
        return $this->clientKey;
    }

    /**
     * Sets the value of clientKey.
     *
     * @param mixed $clientKey the client key
     *
     * @return self
     */
    public function setClientKey($clientKey)
    {
        $this->clientKey = $clientKey;

        return $this;
    }

    /**
     * Gets the value of timestampKey.
     *
     * @return mixed
     */
    public function getTimestampKey()
    {
        return $this->timestampKey;
    }

    /**
     * Sets the value of timestampKey.
     *
     * @param mixed $timestampKey the timestamp key
     *
     * @return self
     */
    public function setTimestampKey($timestampKey)
    {
        $this->timestampKey = $timestampKey;

        return $this;
    }
}