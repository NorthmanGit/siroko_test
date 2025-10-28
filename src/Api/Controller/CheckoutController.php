<?php

namespace App\Api\Controller;

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
