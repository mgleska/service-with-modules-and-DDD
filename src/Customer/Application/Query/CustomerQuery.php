<?php

declare(strict_types=1);

namespace App\Customer\Application\Query;

use App\CommonInfrastructure\Api\ApiProblemException;
use App\Customer\Application\Dto\CustomerDto;
use App\Customer\Application\Validator\CustomerValidator;
use App\Customer\Domain\CustomerRepositoryInterface;

class CustomerQuery
{
    public function __construct(
        private readonly CustomerRepositoryInterface $repository,
        private readonly CustomerValidator $validator,
    ) {
    }

    /**
     * @throws ApiProblemException
     */
    public function getCustomer(int $id): CustomerDto
    {
        $customer = $this->repository->findById($id);
        $this->validator->validateExists($customer);

        return CustomerDto::fromEntity($customer);
    }
}
