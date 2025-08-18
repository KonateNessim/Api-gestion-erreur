<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\JwtService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
  

    #[Route('/api/login', methods: ['POST'])]
    public function login(
        Request $request,
        JwtService $jwtService,
        UserPasswordHasherInterface $hasher,
        UserRepository $userRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = $userRepo->findOneBy(['login' => $data['login']]);

        if (!$user || !$hasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $jwtService->generateToken([
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'roles' => $user->getRoles()
        ]);

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'roles' => $user->getRoles(),
                'is_active' => $user->isActive()
            ],
            'token_expires_in' => $jwtService->getTtl()
        ]);
    }
}
