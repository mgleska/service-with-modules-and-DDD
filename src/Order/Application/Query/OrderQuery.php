<?php

declare(strict_types=1);

namespace App\Order\Application\Query;

use App\CommonInfrastructure\Api\ApiProblemException;
use App\Order\Application\Dto\Order\OrderDto;
use App\Order\Application\Validator\OrderValidator;
use App\Order\Infrastructure\Repository\OrderRepository;
use Exception;

class OrderQuery
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderValidator $orderValidator,
    ) {
    }

    /**
     * @throws ApiProblemException
     * @throws Exception
     */
    public function getOrder(int $id): OrderDto
    {
        $order = $this->orderRepository->find($id);
        $this->orderValidator->validateExists($order);

        return OrderDto::fromEntity($order);
    }
}
