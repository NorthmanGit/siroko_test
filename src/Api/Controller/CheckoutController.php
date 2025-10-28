<?php

namespace App\Api\Controller;

use OpenApi\Attributes as OA;

use App\Application\Command\CheckoutCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\HandleTrait;


class CheckoutController extends AbstractController
{

    use HandleTrait;

    public function __construct(
        private MessageBusInterface $commandBus,
        private MessageBusInterface $queryBus,
        private LoggerInterface $logger
    ) {
        $this->messageBus = $queryBus; 
    }

#[Route('/api/cart/{cartId}/checkout', name: 'cart_checkout', methods: ['POST'])]
#[OA\Post(
    path: '/api/cart/{cartId}/checkout',
    summary: 'Perform checkout and create an order from the cart',
    parameters: [
        new OA\Parameter(
            name: 'cartId',
            in: 'path',
            required: true,
            description: 'Cart UUID to checkout',
            schema: new OA\Schema(type: 'string')
        )
    ],
    responses: [
        new OA\Response(
            response: 201,
            description: 'Checkout completed successfully',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'checkout completed'),
                    new OA\Property(property: 'orderId', type: 'integer', example: 42)
                ]
            )
        ),
        new OA\Response(response: 400, description: 'Validation or business error'),
        new OA\Response(response: 500, description: 'Internal server error')
    ]
)]
public function __invoke(string $cartId, MessageBusInterface $commandBus): JsonResponse
{
    try {
        /** @var string $orderId */
        $orderId = $this->handle(new CheckoutCommand($cartId));

        return $this->json([
            'status' => 'checkout completed',
            'orderId' => $orderId,
        ], 201);
    } catch (\Symfony\Component\Messenger\Exception\ValidationFailedException $e) {
        $this->logger->warning('Validation failed for checkout', [
            'errors' => (string) $e->getViolations(),
        ]);

        return new JsonResponse([
            'error' => 'Validation failed',
            'details' => (string) $e->getViolations(),
        ], 400);
    } catch (\Throwable $e) {
        return $this->json(['error' => $e->getMessage()], 400);
    }
}
}
