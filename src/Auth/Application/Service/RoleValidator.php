<?php

declare(strict_types=1);

namespace App\Auth\Application\Service;

use App\Auth\Application\Enum\ApiProblemTypeEnum;
use App\CommonInfrastructure\Api\ApiProblemException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RoleValidator
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    /**
     * @throws ApiProblemException
     */
    public function validateHasRole(string $role): void
    {
        if ($this->authorizationChecker->isGranted($role)) {
            return;
        }

        throw new ApiProblemException(
            Response::HTTP_FORBIDDEN,
            ApiProblemTypeEnum::VALIDATOR->value,
            'AUTH_ROLE_NOT_GRANTED'
        );
    }
}
