<?php

declare(strict_types=1);

namespace App\CommonInfrastructure\Api;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ApiProblemService
{
    private LoggerInterface $logger;
    private ParameterBagInterface $parameterBag;

    public function __construct(LoggerInterface $logger, ParameterBagInterface $params)
    {
        $this->logger = $logger;
        $this->parameterBag = $params;
    }

    public function handleEvent(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (! $exception instanceof ApiProblemException) {
            if ($exception instanceof HttpException && $exception->getPrevious() instanceof ValidationFailedException) {
                $exception->setHeaders(['Content-Type' => 'application/problem+json']);
            }
            return;
        }

        $response = [
            'type' => $exception->getType(),
            'title' => $exception->getTitle(),
            'status' => $exception->getStatusCode(),
        ];
        if ($this->parameterBag->get('kernel.debug')) {
            $response['trace'] = $exception->getTrace();
        }

        $event->setResponse(
            new JsonResponse(
                $response,
                $exception->getStatusCode(),
                ['Content-Type' => 'application/problem+json']
            )
        );

        $this->logger->error(
            'ApiProblemException: type: {type}, title: {title}, status: {status}, uri: {uri}, file: {file}, line: {line}, trace: {trace}',
            [
                'status' => $exception->getStatusCode(),
                'type' => $exception->getType(),
                'title' => $exception->getTitle(),
                'uri'  => $event->getRequest()->getUri(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => json_encode($exception->getTrace()),
            ]
        );
    }
}
