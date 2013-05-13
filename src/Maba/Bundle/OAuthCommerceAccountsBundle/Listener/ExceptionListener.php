<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Listener;


use Maba\Bundle\OAuthCommerceAccountsBundle\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    /**
     * @var string
     */
    protected $apiKey;

    protected $debug;

    public function __construct($apiKey, $debug = false)
    {
        $this->apiKey = $apiKey;
        $this->debug = $debug;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $apiAttribute = $event->getRequest()->attributes->get('_api');
        if ($apiAttribute === $this->apiKey) {
            if ($exception instanceof ApiException) {
                $data = array(
                    'error' => $exception->getErrorCode(),
                    'error_description' => $exception->getErrorDescription(),
                );
                $statusCode = $exception->getStatusCode();
            } else {
                $data = array(
                    'error' => 'internal_server_error',
                );
                if ($this->debug) {
                    $data['error_description'] = (string) $exception;
                }
                $statusCode = 500;
            }
            $event->setResponse(new JsonResponse($data, $statusCode));
        }
    }
}