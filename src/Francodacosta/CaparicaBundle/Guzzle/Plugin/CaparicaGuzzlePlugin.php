<?php
namespace Francodacosta\CaparicaBundle\Guzzle\Plugin;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Caparica\Client\ClientInterface;
use Caparica\Crypto\SignerInterface;

class CaparicaGuzzlePlugin implements EventSubscriberInterface
{
    CONST PLUGIN_VERSION='0.0.2';

    private $config = [
        'keys' => [
            'timestamp'   => 'X-CAPARICA-TIMESTAMP',
            'signature'   => 'X-CAPARICA-SIG',
            'client'      => 'X-CAPARICA-CLIENT',
            'path'        => 'X-CAPARICA-PATH',
        ]
    ];

    private $caparicaClient;
    private $requestSigner;
    private $includePath = true;

    public function __construct(ClientInterface $client, SignerInterface $requestSigner, $includePath = false)
    {
        $this->caparicaClient = $client;
        $this->requestSigner = $requestSigner;
        $this->includePath = $includePath;
    }

    public function setConfig(array $config)
    {
        $this->config = array_merge_recursive($this->config, $config);
    }

    public static function getSubscribedEvents()
    {
        return array('request.before_send' => 'onBeforeSend');
    }

    public function getParamsToSign(\Guzzle\Http\Message\Request $request)
    {
        //if ('get' == strtolower($request->getMethod())) {
            return $request->getQuery()->toArray();
        //}
    }


    private function getRequestPath(\Guzzle\Http\Message\Request $request) {
        $ret =  str_replace('/app_dev.php', '', $request->getPath());

        return $ret;
    }

    public function onBeforeSend(\Guzzle\Common\Event $event)
    {

        $request = $event['request'];
        $caparicaClient = $this->caparicaClient;
        $requestSigner = $this->requestSigner;
        $timestamp = date('U');

        $paramsToSign = $this->getParamsToSign($request);

        $request->setHeader(
            $this->config['keys']['timestamp'],
            $timestamp
        );

        $request->setHeader(
            $this->config['keys']['client'],
            $caparicaClient->getCode()
        );

        if ($this->includePath) {
            $path = $this->getRequestPath($request);
            $request->setHeader(
                $this->config['keys']['path'],
                $path
            );
            $paramsToSign[$this->config['keys']['path']] = $path;
        }

        $paramsToSign[$this->config['keys']['timestamp']] = $timestamp;

        $signature = $requestSigner->sign($paramsToSign, $caparicaClient->getSecret());

        $request->setHeader(
            $this->config['keys']['signature'],
            $signature
        );

        error_log( 'About to send a request: ' . $event['request'] . "\n");
    }

    /**
     * Gets the value of config.
     *
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Gets the value of caparicaClient.
     *
     * @return mixed
     */
    public function getCaparicaClient()
    {
        return $this->caparicaClient;
    }

    /**
     * Sets the value of caparicaClient.
     *
     * @param mixed $caparicaClient the caparica client
     *
     * @return self
     */
    public function setCaparicaClient($caparicaClient)
    {
        $this->caparicaClient = $caparicaClient;

        return $this;
    }

    /**
     * Gets the value of requestSigner.
     *
     * @return mixed
     */
    public function getRequestSigner()
    {
        return $this->requestSigner;
    }

    /**
     * Sets the value of requestSigner.
     *
     * @param mixed $requestSigner the request signer
     *
     * @return self
     */
    public function setRequestSigner($requestSigner)
    {
        $this->requestSigner = $requestSigner;

        return $this;
    }

    /**
     * Gets the value of includePath.
     *
     * @return mixed
     */
    public function getIncludePath()
    {
        return $this->includePath;
    }

    /**
     * Sets the value of includePath.
     *
     * @param mixed $includePath the include path
     *
     * @return self
     */
    public function setIncludePath($includePath)
    {
        $this->includePath = $includePath;

        return $this;
    }
}