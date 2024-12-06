<?php

declare(strict_types=1);

namespace App\Auth\Application\Service;

use App\Auth\Domain\UserBag;
use App\Auth\Domain\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserBag $userBag,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $user = $this->userRepository->findOneBy(['login' => $accessToken]);
        if (null === $user) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        $this->userBag->setUserId($user->getId());
        $this->userBag->setCustomerId($user->getCustomerId());

        // and return a UserBadge object containing the user identifier from the found token
        // (this is the same identifier used in Security configuration; it can be an email,
        // a UUID, a username, a database ID, etc.)
        return new UserBadge($user->getLogin());
    }
}
