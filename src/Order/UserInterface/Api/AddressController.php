<?php

declare(strict_types=1);

namespace App\Order\UserInterface\Api;

use App\CommonInfrastructure\Api\Dto\ApiProblemResponseDto;
use App\CommonInfrastructure\Api\Dto\SuccessResponseDto;
use App\Order\Application\Command\CreateFixedAddressCmd;
use App\Order\Application\Dto\FixedAddress\CreateFixedAddressDto;
use App\Order\Application\Dto\FixedAddress\FixedAddressDto;
use App\Order\Application\Query\FixedAddressQuery;
use Doctrine\DBAL\Exception as DBALException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AddressController extends AbstractController
{
    #[Route(path: '/address/list', name: 'query-all-addresses', methods: ['GET'], format: 'json')]
    #[OA\Response(
        response: 200,
        description: 'Returns list of fixed addresses suitable for current customer.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: FixedAddressDto::class)))
    )]
    #[OA\Response(response: '400-499', description: 'some exception', content: new Model(type: ApiProblemResponseDto::class))]
    public function getAllFixedAddresses(FixedAddressQuery $provider): JsonResponse
    {
        return new JsonResponse(
            $provider->getAllFixedAddresses()
        );
    }

    #[Route(path: '/address/{id<\d+>}', name: 'query-single-address', methods: ['GET'], format: 'json')]
    #[OA\Response(response: 200, description: 'Returns fixed address data.', content: new Model(type: FixedAddressDto::class))]
    #[OA\Response(response: '400-499', description: 'some exception', content: new Model(type: ApiProblemResponseDto::class))]
    public function getFixedAddress(int $id, FixedAddressQuery $provider): JsonResponse
    {
        return new JsonResponse(
            $provider->getFixedAddress($id)
        );
    }

    /**
     * @throws DBALException
     */
    #[Route(path: '/address/create', name: 'command-create-address', methods: ['POST'], format: 'json')]
    #[OA\Response(response: 200, description: 'Returns identifier of created address.', content: new Model(type: SuccessResponseDto::class))]
    #[OA\Response(response: '400-499', description: 'some exception', content: new Model(type: ApiProblemResponseDto::class))]
    #[IsGranted('ROLE_ADMIN')]
    public function createFixedAddress(
        #[MapRequestPayload] CreateFixedAddressDto $dto,
        CreateFixedAddressCmd $service,
    ): JsonResponse {
        $id = $service->createFixedAddress($dto);

        return new JsonResponse(new SuccessResponseDto(['id' => $id]), Response::HTTP_CREATED);
    }
}
