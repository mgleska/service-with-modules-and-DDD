<?php

declare(strict_types=1);

namespace App\Order\UserInterface\Api;

use App\CommonInfrastructure\Api\Dto\ApiProblemResponseDto;
use App\CommonInfrastructure\Api\Dto\FailResponseDto;
use App\CommonInfrastructure\Api\Dto\SuccessResponseDto;
use App\Order\Application\Command\CreateOrderCmd;
use App\Order\Application\Command\PrintLabelCmd;
use App\Order\Application\Command\SendOrderCmd;
use App\Order\Application\Command\UpdateOrderLinesCmd;
use App\Order\Application\Dto\Order\CreateOrderDto;
use App\Order\Application\Dto\Order\OrderDto;
use App\Order\Application\Dto\Order\UpdateOrderLinesDto;
use App\Order\Application\Query\OrderQuery;
use App\Order\UserInterface\Api\Dto\PrintLabelDto;
use App\Order\UserInterface\Api\Dto\SendOrderDto;
use Exception;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route(path: '/order/{id<\d+>}', name: 'query-single-order', methods: ['GET'], format: 'json')]
    #[OA\Response(response: 200, description: 'Returns order data.', content: new Model(type: OrderDto::class))]
    #[OA\Response(response: '400-499', description: 'some exception', content: new Model(type: ApiProblemResponseDto::class))]
    public function getOrder(int $id, OrderQuery $provider): JsonResponse
    {
        return new JsonResponse(
            $provider->getOrder($id)
        );
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/order/send', name: 'command-send-order', methods: ['POST'], format: 'json')]
    public function sendOrder(
        #[MapRequestPayload] SendOrderDto $dto,
        SendOrderCmd $service,
    ): JsonResponse {
        [$ok, $message] = $service->sendOrder($dto->orderId, $dto->version);
        if ($ok) {
            return new JsonResponse(new SuccessResponseDto(), Response::HTTP_OK);
        } else {
            return new JsonResponse(new FailResponseDto($message), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/order/print-label', name: 'command-print-label', methods: ['POST'], format: 'json')]
    public function printLabel(
        #[MapRequestPayload] PrintLabelDto $dto,
        PrintLabelCmd $service,
    ): Response {
        [$ok, $response] = $service->printLabel($dto->orderId);
        if ($ok) {
            return new Response($response, 200, ['Content-Type' => 'text/plain']);
        } else {
            return new JsonResponse(new FailResponseDto($response), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/order/create', name: 'command-create-order', methods: ['POST'], format: 'json')]
    public function createOrder(
        #[MapRequestPayload] CreateOrderDto $dto,
        CreateOrderCmd $service,
    ): JsonResponse {
        $id = $service->createOrder($dto);

        return new JsonResponse(new SuccessResponseDto(['id' => $id]), Response::HTTP_CREATED);
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/order/update-lines', name: 'command-update-lines', methods: ['POST'], format: 'json')]
    #[OA\Response(response: 200, description: 'Returns order data.', content: new Model(type: OrderDto::class))]
    #[OA\Response(response: '400', description: 'some precondition fail', content: new Model(type: FailResponseDto::class))]
    #[OA\Response(response: '400-499', description: 'some exception', content: new Model(type: ApiProblemResponseDto::class))]
    public function updateOrderLines(
        #[MapRequestPayload] UpdateOrderLinesDto $dto,
        UpdateOrderLinesCmd $service,
    ): JsonResponse {
        [$ok, $response] = $service->updateOrderLines($dto);

        if ($ok) {
            return new JsonResponse($response);
        } else {
            return new JsonResponse(new FailResponseDto($response), Response::HTTP_BAD_REQUEST);
        }
    }
}
