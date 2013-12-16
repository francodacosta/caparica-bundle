<?php

namespace Francodacosta\CaparicaBundle\Tests\Guzzle\Plugin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Francodacosta\CaparicaBundle\Guzzle\Plugin\CaparicaGuzzlePlugin;
use Caparica\Client\BasicClient;
use Caparica\Crypto\RequestSigner;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $caparicaClient = new BasicClient;
        $caparicaClient->setCode('1');
        $caparicaClient->setSecret('not-so-secret1');

        $requestSigner = new RequestSigner;

        $client = new \Guzzle\Service\Client('http://localhost/');

        // Create the plugin and add it as an event subscriber
        $plugin = new CaparicaGuzzlePlugin($caparicaClient, $requestSigner);
        $client->addSubscriber($plugin);

        // Send a request and notice that the request is printed to the screen
        $response = $client->get('app_dev.php/api/doc/')->send();
        var_dump($response->getStatusCode());
        $this->assertEquals(200, $response->getStatusCode());

        echo $response->getBody();
        // $caparicaClient->
    }
}


