<?php

namespace Francodacosta\CaparicaBundle\EventListener;

use Francodacosta\CaparicaBundle\Controller\CaparicaControllerInterface;
use Francodacosta\CaparicaBundle\Controller\CaparicaController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Caparica\Security\RequestValidatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Francodacosta\CaparicaBundle\Exception\InvalidSignatureException;
use Francodacosta\CaparicaBundle\Exception\MissingSignatureException;
use Francodacosta\CaparicaBundle\Exception\MissingClientCodeException;
use Francodacosta\CaparicaBundle\Exception\ControllerNotAvailableException;
use Caparica\Exception\MissingTimestampException;
use Caparica\Exception\OutOfSyncTimestampException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;



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
    private $container;
    private $onErrorRedirectTo;

    private $params;

    const ERROR_INVALID_SIG    = 401;
    const ERROR_INVALID_TS     = 402;
    const ERROR_MISSING_TS     = 403;
    const ERROR_MISSING_SIG    = 404;
    const ERROR_MISSING_CLIENT = 405;


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
            throw new MissingClientCodeException("Missing client code", 400);

        }

        $token = $this->getValue($request, $this->tokenKey);
        if (null == $token) {
            error_log('missing token');
            throw new MissingSignatureException("Missing token", 400);
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
            try {

                if (false === $this->validate($event)) {
                    throw new InvalidSignatureException();
                }

                if ($controller[0] instanceof CaparicaController) {
                    $controller[0]->setClientCode($this->params[$this->clientKey]);
                }
            } catch (\Exception $e) {
                $code = 500;
                if ($e instanceof MissingTimestampException) {
                    $code = self::ERROR_MISSING_TS;
                }
                if ($e instanceof OutOfSyncTimestampException) {
                    $code = self::ERROR_INVALID_TS;
                }
                if ($e instanceof MissingClientCodeException) {
                    $code = self::ERROR_MISSING_CLIENT;
                }
                if ($e instanceof MissingSignatureException) {
                    $code = self::ERROR_MISSING_SIG;
                }
                if ($e instanceof InvalidSignatureException) {
                    $code = self::ERROR_INVALID_SIG;
                }


                throw new ControllerNotAvailableException('', $code);

                // $error_route = $this->getOnErrorRedirectTo();
                // $redirectUrl = $this->getContainer()->get('router')->generate($error_route);
                // // $event->setController(function() use ($redirectUrl) {
                // //     var_dump(new Response($redirectUrl)); die;
                // //     return new Response($redirectUrl);
                // // });
                // // $path['_controller'] = $controller;
                // $subRequest = $this->getContainer()->get('request_stack')->getCurrentRequest()->duplicate(['code' => $code], null, explode('/',$redirectUrl));
                // var_dump($redirectUrl);
                // var_dump($subRequest);
                // die;
                //
                // return $this->getContainer()->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }



        }

    }


    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $kernel = $event->getKernel();

        if (!($exception instanceof ControllerNotAvailableException)) {
            return;
        }

        $attributes = array(
            '_controller' => $this->getOnErrorRedirectTo(),
            'code' => $exception->getCode(),
        );
        $request = $event->getRequest()->duplicate(null, null, $attributes);
        $response = $kernel->handle($request, HttpKernelInterface::SUB_REQUEST, false);

        $event->setResponse($response);
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



    /**
     * Get the value of On Error Redirect To
     *
     * @return mixed
     */
    public function getOnErrorRedirectTo()
    {
        return $this->onErrorRedirectTo;
    }

    /**
     * Set the value of On Error Redirect To
     *
     * @param mixed onErrorRedirectTo
     *
     * @return self
     */
    public function setOnErrorRedirectTo($value)
    {
        $this->onErrorRedirectTo = $value;

        return $this;
    }


    /**
     * Get the value of Container
     *
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the value of Container
     *
     * @param mixed container
     *
     * @return self
     */
    public function setContainer($value)
    {
        $this->container = $value;

        return $this;
    }

}
