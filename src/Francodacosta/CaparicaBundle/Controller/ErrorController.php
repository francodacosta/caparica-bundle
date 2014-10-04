<?php

namespace Francodacosta\CaparicaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
/**
 * Book controller.
 *
 */
class ErrorController extends Controller
{
    public function messageAction(Request $request) {
        $code = $request->get('code');

        switch($code) {
            case \Francodacosta\CaparicaBundle\EventListener\CaparicaTokenListener::ERROR_INVALID_SIG :
                $msg = "Your signature does not match the server generated one for the client code provided";
                $statusMessage = 'Unauthorized';
                $status = 401;
                break;

            case \Francodacosta\CaparicaBundle\EventListener\CaparicaTokenListener::ERROR_INVALID_TS :
                $msg = "The time difference between your timestamp and your server time stamp is too big";
                $statusMessage = 'Bad Request';
                $status = 400;
                break;

            case \Francodacosta\CaparicaBundle\EventListener\CaparicaTokenListener::ERROR_MISSING_TS :
                $msg = "The timestamp was not found in the signature, and your settings made it mandatory";
                $statusMessage = 'Bad Request';
                $status = 400;
                break;

            case \Francodacosta\CaparicaBundle\EventListener\CaparicaTokenListener::ERROR_MISSING_CLIENT :
                $msg = "Client id was not sent in the request";
                $statusMessage = 'Bad Request';
                $status = 400;
                break;

            case \Francodacosta\CaparicaBundle\EventListener\CaparicaTokenListener::ERROR_MISSING_SIG :
                $msg = "The signature was not sent with the request";
                $statusMessage = 'Bad Request';
                $status = 400;
                break;

            default:
                $msg = "An unhandled error was detected, please contact your system administrator";
                $statusMessage = 'Unknown Error';
                $status = 500;
                break;
        }


        $response = $this->render(
            'FrancodacostaCaparicaBundle:Error:message.html.twig',
            [
                'message' => $msg,
                'statusCode' => $status,
                'statusMessage' => $statusMessage,
            ]
        );

        $response->setStatusCode($status);
        return $response;

    }
}
