<?php 

namespace App\Api\Controller;

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
    public function createCart(): JsonResponse
    {
        try {
            $command = new CreateCartCommand();
            $envelope = $this->commandBus->dispatch($command);

            // Suppose the handler returns a UUID (as string)
            $cartId = $envelope->getMessage()->getId();

            // Log success message
            $this->logger->info('Cart created successfully', ['cartId' => $cartId]);

            return new JsonResponse(['cartId' => $cartId], 201);
        } catch (\Throwable $e) {
            // Log error details
            $this->logger->error('Error creating cart', ['exception' => $e]);

            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    #[Route('/api/cart/{id}/item', name: 'add_item_to_cart', methods: ['POST'])]
    public function addItem(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Defensive checks for missing data
            if (!isset($data['productId'], $data['quantity'])) {
                $this->logger->warning('Missing required fields in add item request', ['cartId' => $id]);

                return new JsonResponse(['error' => 'Missing required fields'], 400);
            }

            $command = new AddItemToCartCommand($id, $data['productId'], (int) $data['quantity']);
            $this->commandBus->dispatch($command);

            // Log the item added successfully
            $this->logger->info('Item added to cart', [
                'cartId' => $id,
                'productId' => $data['productId'],
                'quantity' => $data['quantity'],
            ]);

            return new JsonResponse(['status' => 'item added'], 201);

        } catch (\Symfony\Component\Messenger\Exception\ValidationFailedException $e) {
            // Log validation failure
            $this->logger->warning('Validation failed for add item to cart', [
                'cartId' => $id,
                'errors' => (string) $e->getViolations(),
            ]);

            return new JsonResponse([
                'error' => 'Validation failed',
                'details' => (string) $e->getViolations(),
            ], 400);

        } catch (\Throwable $e) {
            // Log unexpected errors
            $this->logger->error('Unexpected error adding item to cart', ['exception' => $e]);

            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    #[Route('/api/cart/{id}', name: 'get_cart', methods: ['GET'])]
    public function getCart(string $id): JsonResponse
    {
        try {
            $query = new GetCartQuery($id);
            $cartView = $this->handle($query);

            if (!$cartView) {
                // Log cart not found
                $this->logger->warning('Cart not found', ['cartId' => $id]);

                return new JsonResponse(['error' => 'Cart not found'], 404);
            }

            // Log successful retrieval of cart
            $this->logger->info('Cart retrieved successfully', ['cartId' => $id]);

            return new JsonResponse($cartView->toArray(), 200);

        } catch (\Symfony\Component\Messenger\Exception\ValidationFailedException $e) {
            // Log validation failure
            $this->logger->warning('Validation failed for get cart query', [
                'cartId' => $id,
                'errors' => (string) $e->getViolations(),
            ]);

            return new JsonResponse([
                'error' => 'Validation failed',
                'details' => (string) $e->getViolations(),
            ], 400);

        } catch (\Throwable $e) {
            // Log unexpected errors
            $this->logger->error('Unexpected error retrieving cart', ['exception' => $e]);

            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }
    #[Route('/api/cart/{id}/item/{productId}', name: 'remove_item_from_cart', methods: ['DELETE'])]
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
            // Log validation failure
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
