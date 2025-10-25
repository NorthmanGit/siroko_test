<?php
namespace App\Api\Controller;

use App\Application\Command\AddItemToCartCommand;
use App\Application\Command\Handler\AddItemToCartHandler;
use App\Application\Query\GetCartQuery;
use App\Application\Query\Handler\GetCartHandler;
use App\Application\Command\CreateCartCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\HandleTrait;

class CartController
{
    use HandleTrait;

    public function __construct(
        private MessageBusInterface $commandBus,
        private MessageBusInterface $queryBus,
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

            return new JsonResponse(['cartId' => $cartId], 201);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    #[Route('/api/cart/{id}/items', name: 'add_item_to_cart', methods: ['POST'])]
    public function addItem(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
    
            // Defensive checks for missing data
            if (!isset($data['productId'], $data['quantity'])) {
                return new JsonResponse(['error' => 'Missing required fields'], 400);
            }
    
            $command = new AddItemToCartCommand($id, $data['productId'], (int) $data['quantity']);
            $this->commandBus->dispatch($command);
    
            return new JsonResponse(['status' => 'item added'], 201);
    
        } catch (\Symfony\Component\Messenger\Exception\ValidationFailedException $e) {
            // Messenger validation middleware caught invalid data
            return new JsonResponse([
                'error' => 'Validation failed',
                'details' => (string) $e->getViolations(),
            ], 400);
    
        }catch (\Throwable $e) {
            // Anything else (unexpected)
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
                return new JsonResponse(['error' => 'Cart not found'], 404);
            }
    
            return new JsonResponse($cartView->toArray(), 200);
    
        } catch (\Symfony\Component\Messenger\Exception\ValidationFailedException $e) {
            return new JsonResponse([
                'error' => 'Validation failed',
                'details' => (string) $e->getViolations(),
            ], 400);
    
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }

}