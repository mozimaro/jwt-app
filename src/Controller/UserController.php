<?php

// src/Controller/UserController.php

namespace App\Controller;

use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private LoggerInterface $logger;

    // constructor injection
    public function __construct(UserRepository $userRepository, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    #[Route('/api/users', name: 'get_users', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        try {
            $users = $this->userRepository->getUsers();

            // map les users en tableau simple
            $usersArray = array_map(function ($user) {
                return [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),

                ];
            }, $users);

            return new JsonResponse([
                'status' => 'success',
                'message' => 'get users success',
                'data' => [
                    'users' => $usersArray
                ]
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            $this->logger->error('Erreur fi getUsers: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return new JsonResponse([
                'status' => 'fail',
                'message' => 'internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
