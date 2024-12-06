<?php

declare(strict_types=1);

namespace App\Admin\UserInterface\EventHandler;

use App\Admin\Application\Command\SwitchDatabaseCmd;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class OnKernelControllerHandler
{
    public function __construct(
        private readonly SwitchDatabaseCmd $service,
    ) {
    }

    /**
     * @throws DBALException
     * @noinspection PhpUnusedParameterInspection
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $this->service->switchDatabase();
    }
}
