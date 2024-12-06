<?php

declare(strict_types=1);

namespace App\Order\Application\Command;

use App\CommonInfrastructure\Api\ApiProblemException;
use App\CommonInfrastructure\GenericDtoValidator;
use App\Order\Application\Dto\Order\OrderDto;
use App\Order\Application\Dto\Order\OrderLineDto;
use App\Order\Application\Dto\Order\UpdateOrderLinesDto;
use App\Order\Application\Enum\ApiProblemTypeEnum;
use App\Order\Application\Validator\OrderValidator;
use App\Order\Domain\OrderLine;
use App\Order\Domain\OrderStatusEnum;
use App\Order\Infrastructure\Repository\OrderRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

use function array_key_exists;
use function count;

class UpdateOrderLinesCmd
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderValidator $validator,
        private readonly LoggerInterface $logger,
        private readonly GenericDtoValidator $dtoValidator,
    ) {
    }

    /**
     * @return array{bool, string|OrderDto}
     * @throws Exception
     */
    public function updateOrderLines(UpdateOrderLinesDto $dto): array
    {
        $this->dtoValidator->validate($dto, __FUNCTION__);

        $order = $this->orderRepository->getWithLock($dto->orderId);
        $this->validator->validateExists($order);

        if ($order->getVersion() !== $dto->version) {
            return [false, 'ORDER_VERSION_IN_DATABASE_IS_DIFFERENT'];
        }

        if ($order->getStatus() !== OrderStatusEnum::NEW) {
            return [false, 'ORDER_STATUS_NOT_VALID_FOR_UPDATE_LINES'];
        }

        $isModified = false;
        $linesToAdd = [];
        $linesToUpdate = [];

        foreach ($dto->lines as $lineDto) {
            if ($lineDto->id === null) {
                $linesToAdd[] = $lineDto;
                $isModified = true;
                continue;
            }
            $linesToUpdate[$lineDto->id] = $lineDto;
        }

        foreach ($order->getLines() as $key => $line) {
            if (! array_key_exists($line->getId(), $linesToUpdate)) {
                $order->removeLine($line);
                $this->orderRepository->removeLine($line);
                $isModified = true;
                unset($linesToUpdate[$line->getId()]);
                continue;
            }

            if ($this->isModifiedLine($line, $linesToUpdate[$line->getId()])) {
                $order->updateLine($key, $this->createOrderLine($linesToUpdate[$line->getId()]));
                $isModified = true;
            }

            unset($linesToUpdate[$line->getId()]);
        }

        if (count($linesToUpdate) > 0) {
            throw new ApiProblemException(
                Response::HTTP_BAD_REQUEST,
                ApiProblemTypeEnum::UPDATE_LINES->value,
                'ORDER_UPDATE_LINES_LINE_WITH_INVALID_ID'
            );
        }

        foreach ($linesToAdd as $lineDto) {
            $order->addLine($this->createOrderLine($lineDto));
        }

        if ($isModified) {
            $this->orderRepository->save($order, true);
            $this->logger->info('Updated lines of order with id {id} and number {nr}.', ['id' => $order->getId(), 'nr' => $order->getNumber()]);
        } else {
            $this->logger->info(
                'Requested update of lines of order with id {id} and number {nr} but nothing changed.',
                ['id' => $order->getId(), 'nr' => $order->getNumber()]
            );
        }

        return [true, OrderDto::fromEntity($order)];
    }

    private function isModifiedLine(OrderLine $entity, OrderLineDto $lineDto): bool
    {
        $isModified = false;
        if (
            $lineDto->height !== $entity->getHeight()
            || $lineDto->width !== $entity->getWidth()
            || $lineDto->length !== $entity->getLength()
            || $lineDto->quantity !== $entity->getQuantity()
            || (int)round($lineDto->weightOnePallet * 100) !== $entity->getWeightOnePallet()
            || $lineDto->goodsDescription !== $entity->getGoodsDescription()
        ) {
            $isModified = true;
        }

        return $isModified;
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
