<?php

declare(strict_types=1);

namespace App\Customer\UserInterface\EventListener;

use App\Customer\Application\Service\BagService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly BagService $service,
    ) {
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {

        $this->service->fillBag();
    }

    public static function getSubscribedEvents(): array
    {
        return [LoginSuccessEvent::class => 'onLoginSuccess'];
    }
}
