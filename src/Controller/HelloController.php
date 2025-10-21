<?php
// src/Controller/HelloController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HelloController
{
    #[Route('/api/hello', name: 'api_hello', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['message' => 'Symfony is running inside Docker']);
    }
}
