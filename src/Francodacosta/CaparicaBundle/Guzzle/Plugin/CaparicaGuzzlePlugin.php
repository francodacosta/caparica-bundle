<?php
namespace Francodacosta\CaparicaBundle\Guzzle\Plugin;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Caparica\Client\ClientInterface;
use Caparica\Crypto\SignerInterface;

class CaparicaGuzzlePlugin implements EventSubscriberInterface
{
    CONST PLUGIN_VERSION='0.0.1';

    private $config = [
        'keys' => [
            'timestamp' => 'X-CAPARICA-TIMESTAMP',
            'signature'  => 'X-CAPARICA-SIG',
            'client'     => 'X-CAPARICA-CLIENT',
        ]
    ];

    private $caparicaClient;
    private $requestSigner;


    public function __construct(ClientInterface $client, SignerInterface $requestSigner)
    {
        $this->caparicaClient = $client;
        $this->requestSigner = $requestSigner;
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
        if ('get' == strtolower($request->getMethod())) {
            return $request->getQuery()->toArray();
        }
    }

    public function onBeforeSend(\Guzzle\Common\Event $event)
    {

        $request = $event['request'];
        $caparicaClient = $this->caparicaClient;
        $requestSigner = $this->requestSigner;
        $timestamp = date('U');

        $request->setHeader(
            $this->config['keys']['timestamp'],
            $timestamp
        );

        $request->setHeader(
            $this->config['keys']['client'],
            $caparicaClient->getCode()
        );

        $paramsToSign = $this->getParamsToSign($request);
        $paramsToSign[$this->config['keys']['timestamp']] = $timestamp;

        $signature = $requestSigner->sign($paramsToSign, $caparicaClient->getSecret());

        $request->setHeader(
            $this->config['keys']['signature'],
            $signature
        );

        // error_log( 'About to send a request: ' . $event['request'] . "\n");
    }
}