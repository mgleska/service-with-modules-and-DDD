<?php

declare(strict_types=1);

namespace App\Order\Application\Command;

use App\Order\Application\Validator\OrderValidator;
use App\Order\Domain\OrderStatusEnum;
use App\Order\Infrastructure\Repository\OrderRepository;
use Exception;
use Psr\Log\LoggerInterface;

class SendOrderCmd
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderValidator $validator,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return array{bool, string}
     * @throws Exception
     */
    public function sendOrder(int $orderId, int $version): array
    {
        $order = $this->orderRepository->getWithLock($orderId);
        $this->validator->validateExists($order);

        if ($order->getVersion() !== $version) {
            return [false, 'ORDER_VERSION_IN_DATABASE_IS_DIFFERENT'];
        }

        if ($order->getStatus() !== OrderStatusEnum::NEW) {
            return [false, 'ORDER_STATUS_NOT_VALID_FOR_SEND'];
        }

        $order->changeStatus(OrderStatusEnum::SENT);
        $this->orderRepository->save($order, true);

        $this->logger->info('Order with id {id} sent.', ['id' => $orderId]);

        return [true, ''];
    }
}
