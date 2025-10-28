<?php 

namespace App\Api\Controller;

use OpenApi\Attributes as OA;

use App\Application\Command\AddItemToCartCommand;
use App\Application\Query\GetCartQuery;
use App\Application\Command\CreateCartCommand;
use App\Application\Command\RemoveItemFromCartCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\HandleTrait;
use App\Application\Command\UpdateCartItemQuantityCommand;

class CartController
{
    use HandleTrait;

    public function __construct(
        private MessageBusInterface $commandBus,
        private MessageBusInterface $queryBus,
        private LoggerInterface $logger // Inject the logger
    ) {
        $this->messageBus = $queryBus; // for HandleTrait
    }

    
    #[Route('/api/cart', name: 'create_cart', methods: ['POST'])]
    #[OA\Post(
        path: '/api/cart',
        summary: 'Create a new cart',
        responses: [
            new OA\Response(
                response: 201,
                description: 'Cart created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'cartId', type: 'string', example: 'b7e0e2c0-0b7f-4c3f-8a3e-8f9c9f7a12f5'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function createCart(): JsonResponse
    {
        try {
            $command = new CreateCartCommand();
            $envelope = $this->commandBus->dispatch($command);

            $cartId = $envelope->getMessage()->getId();

            $this->logger->info('Cart created successfully', ['cartId' => $cartId]);

            return new JsonResponse(['cartId' => $cartId], 201);
        } catch (\Throwable $e) {
            $this->logger->error('Error creating cart', ['exception' => $e]);

            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    #[Route('/api/cart/{id}/item', name: 'add_item_to_cart', methods: ['POST'])]
    #[OA\Post(
        path: '/api/cart/{id}/item',
        summary: 'Add an item to the cart',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['productId', 'quantity'],
                properties: [
                    new OA\Property(property: 'productId', type: 'string', example: 'SKU123'),
                    new OA\Property(property: 'quantity', type: 'integer', example: 2)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Item added successfully'),
            new OA\Response(response: 400, description: 'Validation failed'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function addItem(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['productId'], $data['quantity'])) {
                $this->logger->warning('Missing required fields in add item request', ['cartId' => $id]);

                return new JsonResponse(['error' => 'Missing required fields'], 400);
            }

            $command = new AddItemToCartCommand($id, $data['productId'], (int) $data['quantity']);
            $this->commandBus->dispatch($command);

            $this->logger->info('Item added to cart', [
                'cartId' => $id,
                'productId' => $data['productId'],
                'quantity' => $data['quantity'],
            ]);

            return new JsonResponse(['status' => 'item added'], 201);

        } catch (\Symfony\Component\Messenger\Exception\ValidationFailedException $e) {
            $this->logger->warning('Validation failed for add item to cart', [
                'cartId' => $id,
                'errors' => (string) $e->getViolations(),
            ]);

            return new JsonResponse([
                'error' => 'Validation failed',
                'details' => (string) $e->getViolations(),
            ], 400);

        } catch (\Throwable $e) {
            $this->logger->error('Unexpected error adding item to cart', ['exception' => $e]);

            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    #[Route('/api/cart/{id}', name: 'get_cart', methods: ['GET'])]
    #[OA\Get(
        path: '/api/cart/{id}',
        summary: 'Retrieve a cart and its items',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', example: 'b7e0e2c0-0b7f-4c3f-8a3e-8f9c9f7a12f5'),
                        new OA\Property(property: 'items', type: 'array', items:
                            new OA\Items(properties: [
                                new OA\Property(property: 'productId', type: 'string', example: 'SKU123'),
                                new OA\Property(property: 'quantity', type: 'integer', example: 2)
                            ])
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Cart not found')
        ]
    )]
    public function getCart(string $id): JsonResponse
    {
        try {
            $query = new GetCartQuery($id);
            $cartView = $this->handle($query);

            if (!$cartView) {
                $this->logger->warning('Cart not found', ['cartId' => $id]);

                return new JsonResponse(['error' => 'Cart not found'], 404);
            }

            $this->logger->info('Cart retrieved successfully', ['cartId' => $id]);

            return new JsonResponse($cartView->toArray(), 200);

        } catch (\Symfony\Component\Messenger\Exception\ValidationFailedException $e) {
            $this->logger->warning('Validation failed for get cart query', [
                'cartId' => $id,
                'errors' => (string) $e->getViolations(),
            ]);

            return new JsonResponse([
                'error' => 'Validation failed',
                'details' => (string) $e->getViolations(),
            ], 400);

        } catch (\Throwable $e) {
            $this->logger->error('Unexpected error retrieving cart', ['exception' => $e]);

            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }
    #[Route('/api/cart/{id}/item/{productId}', name: 'remove_item_from_cart', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/cart/{id}/item/{productId}',
        summary: 'Remove an item from the cart',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'productId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Item removed'),
            new OA\Response(response: 400, description: 'Validation failed'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function removeItem(string $id, string $productId): JsonResponse
    {
        try {
            $command = new RemoveItemFromCartCommand($id, $productId);
            $this->commandBus->dispatch($command);

            $this->logger->info('Item removed from cart', [
                'cartId' => $id,
                'productId' => $productId,
            ]);

            return new JsonResponse(['status' => 'item removed'], 200);
        } catch (\Symfony\Component\Messenger\Exception\ValidationFailedException $e) {
            $this->logger->warning('Validation failed for delete item query', [
                'cartId' => $id,
                'productId' => $productId,
                'errors' => (string) $e->getViolations(),
            ]);

            return new JsonResponse([
                'error' => 'Validation failed',
                'details' => (string) $e->getViolations(),
            ], 400);

        }catch (\Throwable $e) {
            $this->logger->error('Error removing item from cart', ['exception' => $e]);
            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }
    #[Route('/api/cart/{id}/item/{productId}', name: 'update_item_quantity', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/cart/{id}/item/{productId}',
        summary: 'Update quantity of an item in the cart',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'productId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quantity'],
                properties: [
                    new OA\Property(property: 'quantity', type: 'integer', example: 3)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Quantity updated'),
            new OA\Response(response: 400, description: 'Missing quantity or validation failed'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function updateItemQuantity(string $id, string $productId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!isset($data['quantity'])) {
                return new JsonResponse(['error' => 'Missing quantity'], 400);
            }
    
            $command = new UpdateCartItemQuantityCommand($id, $productId, (int) $data['quantity']);
            $this->commandBus->dispatch($command);
    
            $this->logger->info('Item quantity updated', [
                'cartId' => $id,
                'productId' => $productId,
                'newQuantity' => $data['quantity']
            ]);
    
            return new JsonResponse(['status' => 'quantity updated'], 200);
    
        } catch (\Throwable $e) {
            $this->logger->error('Error updating item quantity', ['exception' => $e]);
            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }
    
}
