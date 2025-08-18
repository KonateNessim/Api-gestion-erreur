<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;


class UserController extends AbstractController
{
    #[Route('/api/user/{id}/toggle-active', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/users/{id}/toggle-active',
        summary: 'Activer ou désactiver un utilisateur',
        tags: ['user'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Statut modifié'),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé')
        ]
    )]
    #[OA\Tag(name: 'user')]
    public function toggleActive(User $user, UserRepository $repository): JsonResponse
    {
        $repository->updateActiveStatus($user, !$user->isActive());
        return $this->json(['message' => 'Status updated']);
    }

  
}