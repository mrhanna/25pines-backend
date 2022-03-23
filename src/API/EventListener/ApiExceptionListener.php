<?php

namespace App\API\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        if (preg_match('/^\/api\/*/', $event->getRequest()?->getRequestUri())) {
            $exception = $event->getThrowable();

            if ($exception instanceof HttpExceptionInterface) {
                $response = new JsonResponse(
                    [
                        'code' => $exception->getStatusCode(),
                        'message' => $exception->getMessage(),
                    ],
                    $exception->getStatusCode(),
                    $exception->getHeaders()
                );

                $event->setResponse($response);
            }
        }
    }
}
