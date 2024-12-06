<?php

declare(strict_types=1);

namespace App\Order\Application\Command;

use App\Auth\Domain\UserBag;
use App\CommonInfrastructure\GenericDtoValidator;
use App\Order\Application\Dto\Order\CreateOrderDto;
use App\Order\Application\Dto\Order\OrderLineDto;
use App\Order\Application\Validator\FixedAddressValidator;
use App\Order\Application\Validator\OrderValidator;
use App\Order\Domain\FixedAddress;
use App\Order\Domain\FixedAddressRepositoryInterface;
use App\Order\Domain\Order;
use App\Order\Domain\OrderLine;
use App\Order\Domain\OrderRepositoryInterface;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class CreateOrderCmd
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderValidator $orderValidator,
        private readonly LoggerInterface $logger,
        private readonly UserBag $userBag,
        private readonly GenericDtoValidator $dtoValidator,
        private readonly FixedAddressRepositoryInterface $addressRepository,
        private readonly FixedAddressValidator $addressValidator,
    ) {
    }

    /**
     * @throws ValidationFailedException
     * @throws Exception
     */
    public function createOrder(CreateOrderDto $dto): int
    {
        $this->dtoValidator->validate($dto, __FUNCTION__);

        if ($dto->loadingFixedAddressExternalId !== null) {
            $fixedAddress = $this->addressRepository->findOneBy(['externalId' => $dto->loadingFixedAddressExternalId]);
            $this->addressValidator->validateExists($fixedAddress);
        } else {
            $fixedAddress = null;
        }

        $this->orderValidator->validateLoadingAddressForCreate($fixedAddress, $dto->loadingAddress);

        $order = $this->createOrderHeader($dto, $fixedAddress);

        foreach ($dto->lines as $lineDto) {
            $line = $this->createOrderLine($lineDto);
            $order->addLine($line);
        }

        $this->orderRepository->save($order, true);

        $this->logger->info('Created order with id {id} and number {nr}.', ['id' => $order->getId(), 'nr' => $order->getNumber()]);

        return $order->getId();
    }

    /**
     * @throws Exception
     */
    private function createOrderHeader(CreateOrderDto $dto, FixedAddress|null $fixedAddress): Order
    {
        $order = new Order($this->orderNumberGenerator());
        $order->setLoadingDate(new DateTime($dto->loadingDate));
        if ($fixedAddress !== null) {
            $order->setLoadingFixedAddressExternalId($dto->loadingFixedAddressExternalId);
            $order->setLoadingNameCompanyOrPerson($fixedAddress->getNameCompanyOrPerson());
            $order->setLoadingAddress($fixedAddress->getAddress());
            $order->setLoadingCity($fixedAddress->getCity());
            $order->setLoadingZipCode($fixedAddress->getZipCode());
        } else {
            $order->setLoadingFixedAddressExternalId(null);
            $order->setLoadingNameCompanyOrPerson($dto->loadingAddress->nameCompanyOrPerson);
            $order->setLoadingAddress($dto->loadingAddress->address);
            $order->setLoadingCity($dto->loadingAddress->city);
            $order->setLoadingZipCode($dto->loadingAddress->zipCode);
        }
        $order->setLoadingContactPerson($dto->loadingContact->contactPerson);
        $order->setLoadingContactPhone($dto->loadingContact->contactPhone);
        $order->setLoadingContactEmail($dto->loadingContact->contactEmail);
        $order->setDeliveryNameCompanyOrPerson($dto->deliveryAddress->nameCompanyOrPerson);
        $order->setDeliveryAddress($dto->deliveryAddress->address);
        $order->setDeliveryCity($dto->deliveryAddress->city);
        $order->setDeliveryZipCode($dto->deliveryAddress->zipCode);
        $order->setDeliveryContactPerson($dto->deliveryContact->contactPerson);
        $order->setDeliveryContactPhone($dto->deliveryContact->contactPhone);
        $order->setDeliveryContactEmail($dto->deliveryContact->contactEmail);

        return $order;
    }

    private function orderNumberGenerator(): string
    {
        do {
            $nr = $this->userBag->getCustomerId() . '/' . date('Ymd') . '/' . rand(1, 9999);
            $count = $this->orderRepository->count(['number' => $nr]);
        } while ($count > 0);

        return $nr;
    }

    private function createOrderLine(OrderLineDto $lineDto): OrderLine
    {
        $entity = new OrderLine();
        $entity->setQuantity($lineDto->quantity);
        $entity->setLength($lineDto->length);
        $entity->setWidth($lineDto->width);
        $entity->setHeight($lineDto->height);
        $weight = (int)round($lineDto->weightOnePallet * 100);
        $entity->setWeightOnePallet($weight);
        $entity->setWeightTotal($lineDto->quantity * $weight);
        $entity->setGoodsDescription($lineDto->goodsDescription);

        return $entity;
    }
}
