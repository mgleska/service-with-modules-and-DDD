<?php

declare(strict_types=1);

namespace App\Order\Application\Command;

use App\Order\Application\Validator\OrderValidator;
use App\Order\Domain\Order;
use App\Order\Domain\OrderStatusEnum;
use App\Order\Infrastructure\Repository\OrderRepository;
use App\Printer\Application\Command\PrintLabelCmd as PrintPrintLabel;
use App\Printer\Application\Dto\AddressDto as PrintAddressDto;
use App\Printer\Application\Dto\GoodsLineDto as PrintGoodsLineDto;
use App\Printer\Application\Dto\PrintLabelDto;
use App\Printer\Application\Dto\SsccDto as PrintSsccDto;
use Exception;
use Psr\Log\LoggerInterface;

class PrintLabelCmd
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly PrintPrintLabel $printLabelCmd,
        private readonly OrderValidator $orderValidator,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return array{bool, string}
     * @throws Exception
     */
    public function printLabel(int $orderId): array
    {
        $order = $this->orderRepository->getWithLock($orderId);
        $this->orderValidator->validateExists($order);

        if ($order->getStatus() !== OrderStatusEnum::CONFIRMED) {
            return [false, 'ORDER_PRINT_LABEL_STATUS_NOT_VALID_FOR_PRINT'];
        }

        $label = $this->printLabelCmd->printLabel($this->prepareLabelData($order));

        $order->changeStatus(OrderStatusEnum::PRINTED);
        $this->orderRepository->save($order, true);

        $this->logger->info('Order with id {id} printed.', ['id' => $orderId]);

        return [true, $label];
    }

    private function prepareLabelData(Order $order): PrintLabelDto
    {
        $loadingAddress = new PrintAddressDto(
            substr($order->getLoadingNameCompanyOrPerson(), 0, 40),
            substr($order->getLoadingAddress(), 0, 40),
            substr($order->getLoadingZipCode(), 0, 15),
            substr($order->getLoadingCity(), 0, 25),
        );

        $deliveryAddress = new PrintAddressDto(
            substr($order->getDeliveryNameCompanyOrPerson(), 0, 40),
            substr($order->getDeliveryAddress(), 0, 40),
            substr($order->getDeliveryZipCode(), 0, 15),
            substr($order->getDeliveryCity(), 0, 25),
        );

        $lines = [];
        foreach ($order->getLines() as $line) {
            $lines[] = new PrintGoodsLineDto(
                substr($line->getGoodsDescription(), 0, 25),
                $line->getQuantity(),
            );
        }

        $ssccs = [];
        foreach ($order->getSsccs() as $item) {
            $ssccs[] = new PrintSsccDto(
                $item->getCode(),
            );
        }

        return new PrintLabelDto($loadingAddress, $deliveryAddress, $lines, $ssccs);
    }
}
