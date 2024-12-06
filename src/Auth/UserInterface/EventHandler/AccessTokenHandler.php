<?php

declare(strict_types=1);

namespace App\Auth\UserInterface\EventHandler;

use App\Auth\Application\Service\AccessTokenService;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private readonly AccessTokenService $service,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        return $this->service->getUserBadgeFrom($accessToken);
    }
}
