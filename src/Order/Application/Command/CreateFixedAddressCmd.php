<?php

declare(strict_types=1);

namespace App\Order\Application\Command;

use App\Auth\Application\Service\RoleValidator;
use App\CommonInfrastructure\Api\ApiProblemException;
use App\CommonInfrastructure\DatabaseService;
use App\CommonInfrastructure\GenericDtoValidator;
use App\Customer\Application\Query\CustomerQuery;
use App\Customer\Domain\CustomerBag;
use App\Order\Application\Dto\FixedAddress\CreateFixedAddressDto;
use App\Order\Application\Validator\FixedAddressValidator;
use App\Order\Domain\FixedAddress;
use App\Order\Domain\FixedAddressRepositoryInterface;
use Doctrine\DBAL\Exception as DBALException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class CreateFixedAddressCmd
{
    public function __construct(
        private readonly FixedAddressRepositoryInterface $addressRepository,
        private readonly FixedAddressValidator $validator,
        private readonly GenericDtoValidator $dtoValidator,
        private readonly LoggerInterface $logger,
        private readonly DatabaseService $databaseService,
        private readonly CustomerQuery $customerQuery,
        private readonly CustomerBag $customerBag,
        private readonly RoleValidator $roleValidator,
    ) {
    }

    /**
     * @throws ApiProblemException
     * @throws DBALException
     * @throws ValidationFailedException
     */
    public function createFixedAddress(CreateFixedAddressDto $dto): int
    {
        $this->dtoValidator->validate($dto, __FUNCTION__);
        $this->roleValidator->validateHasRole('ROLE_ADMIN');

        $customerDto = $this->customerQuery->getCustomer($dto->customerId);

        $this->databaseService->switchDatabase($customerDto->dbNameSuffix);

        $this->validator->validateExternalIdNotUsed($dto->externalId);

        $address = new FixedAddress();
        $address
            ->setExternalId($dto->externalId)
            ->setNameCompanyOrPerson($dto->nameCompanyOrPerson)
            ->setAddress($dto->address)
            ->setCity($dto->city)
            ->setZipCode($dto->zipCode);

        $this->addressRepository->save($address, true);

        $this->databaseService->switchDatabase($this->customerBag->getDatabaseSuffix());

        $this->logger->info('Created fixed address with id {id} for customer with id {custId}.', ['id' => $address->getId(), 'custIs' => $dto->customerId]);

        return $address->getId();
    }
}
