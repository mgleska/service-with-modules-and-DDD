<?php

declare(strict_types=1);

namespace App\CommonInfrastructure\Api;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ApiProblemExceptionHandler
{
    public function __construct(
        private readonly ApiProblemService $service,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $this->service->handleEvent($event);
    }
}
