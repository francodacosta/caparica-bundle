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
namespace Francodacosta\CaparicaBundle\EventListener;

use Francodacosta\CaparicaBundle\Controller\CaparicaControllerInterface;
use Francodacosta\CaparicaBundle\Controller\CaparicaController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Caparica\Security\RequestValidatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CaparicaTokenListener
{
    private $caparicaRequestValidator;

    private $tokenKey;
    private $clientKey;
    private $timestampKey;
    private $pathKey;
    private $methodKey;
    private $includePathInSignature = true;
    private $includeMethodInSignature = true;

    private $params;

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
        unset($params[$this->pathKey]);
        unset($params[$this->methodKey]);

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

        if ($this->getIncludePathInSignature() ) {
            $params[$this->pathKey] = $request->getPathInfo();
            // error_log('$request->getPathInfo() ' . $request->getPathInfo());
        }

        if ($this->getIncludeMethodInSignature() ) {
            $params[$this->methodKey] = $request->getMethod();
            // error_log('$request->getMethod() ' . $request->getMethod());
        }

        if (null == $params[$this->timestampKey] ) {
            // throw new \InvalidArgumentException("Missing timestamp", 400);
            unset($params[$this->timestampKey] );
        }

        $clientId = $this->getValue($request, $this->clientKey);
        if (null == $clientId) {
            error_log("missing client code");
            throw new \InvalidArgumentException("Missing client code", 400);

        }

        $token = $this->getValue($request, $this->tokenKey);
        if (null == $token) {
            error_log('missing token');
            throw new \InvalidArgumentException("Missing token", 400);
        }

        $this->params = $params;
        $this->params[$this->clientKey] = $clientId;

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
                $msg = 'Your signature does not match server computed signature';
                error_log('[CAPARICA] ' . $msg );
                throw new HttpException(401, $msg);

                $error_route = $this->container->getParameter('francodacosta.caparica.on.error.redirect.to');
                $redirectUrl = $this->router->generate($error_route, ['code' => 401, 'msg' => $msg]);
                $event->setController(function() use ($redirectUrl) {
                    return new RedirectResponse($redirectUrl);
                });
            }

            if ($controller[0] instanceof CaparicaController) {
                $controller[0]->setClientCode($this->params[$this->clientKey]);
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

    /**
     * Gets the value of pathKey.
     *
     * @return mixed
     */
    public function getPathKey()
    {
        return $this->pathKey;
    }

    /**
     * Sets the value of pathKey.
     *
     * @param mixed $pathKey the path key
     *
     * @return self
     */
    public function setPathKey($pathKey)
    {
        $this->pathKey = $pathKey;

        return $this;
    }



    /**
     * Gets the value of includePathInSignature.
     *
     * @return mixed
     */
    public function getIncludePathInSignature()
    {
        return $this->includePathInSignature;
    }

    /**
     * Sets the value of includePathInSignature.
     *
     * @param mixed $includePathInSignature the include path in signature
     *
     * @return self
     */
    public function setIncludePathInSignature($includePathInSignature)
    {
        $this->includePathInSignature = $includePathInSignature;

        return $this;
    }

    /**
     * Gets the value of methodKey.
     *
     * @return mixed
     */
    public function getMethodKey()
    {
        return $this->methodKey;
    }

    /**
     * Sets the value of methodKey.
     *
     * @param mixed $methodKey the method key
     *
     * @return self
     */
    public function setMethodKey($methodKey)
    {
        $this->methodKey = $methodKey;

        return $this;
    }

    /**
     * Gets the value of includeMethodInSignature.
     *
     * @return mixed
     */
    public function getIncludeMethodInSignature()
    {
        return $this->includeMethodInSignature;
    }

    /**
     * Sets the value of includeMethodInSignature.
     *
     * @param mixed $includeMethodInSignature the include method in signature
     *
     * @return self
     */
    public function setIncludeMethodInSignature($includeMethodInSignature)
    {
        $this->includeMethodInSignature = $includeMethodInSignature;

        return $this;
    }

    /**
     * Gets the value of params.
     *
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Sets the value of params.
     *
     * @param mixed $params the params
     *
     * @return self
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }
}
