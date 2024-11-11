<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof NotFoundHttpException) {
            $responseContent = [
                'status' => 'error',
                'statusCode' => 404,
                'message' => 'The requested resource could not be found.',
                'metadata' => [
                    'requestId' => uniqid(),
                    'requestDuration' => round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000) . 'ms',
                ],
            ];

            $response = new JsonResponse($responseContent, 404);
            $event->setResponse($response);
        }
    }
}