<?php

namespace App\Controller;

use App\Factory\UserFactory;
use App\Type\UserRole;
use App\Validator\RegisterSchema;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Validator\LoginSchema;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthController extends AbstractController
{
    private UserRepository $userRepository;
    private ValidatorInterface $validator;
    private JWTTokenManagerInterface $jwtManager; // ← خزّن property
    private UserPasswordHasherInterface $passwordHasher;
    public function __construct(
        UserRepository $userRepository,
        ValidatorInterface $validator,
        JWTTokenManagerInterface $jwtManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        $this->jwtManager = $jwtManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, LoggerInterface $logger): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validate input using RegisterSchema
            $schema = new RegisterSchema();
            $schema->username = $data['username'] ?? null;
            $schema->email = $data['email'] ?? null;
            $schema->password = $data['password'] ?? null;
            $schema->role = $data['role'] ?? null;

            $errors = $this->validator->validate($schema);
            if (count($errors) > 0) {
                $firstError = $errors->get(0)->getMessage();
                return new JsonResponse(['status' => 'fail', 'message' => $firstError], 400);
            }

            // Check if email already exists
            $existingUser = $this->userRepository->getUserByEmail($schema->email);
            if ($existingUser) {
                return new JsonResponse(['status' => 'fail', 'message' => 'Email already exists'], 409);
            }

            // === Build User with Factory ===
            $role = $schema->role ? UserRole::from($schema->role) : null;

            $user = UserFactory::create(
                $schema->email,
                $schema->username,
                $schema->password,
                $role
            );

            // === Save User in DB ===
            $this->userRepository->register($user);

            // === Generate JWT token ===

            $token = $this->jwtManager->createFromPayload($user, [
                'email' => $user->getEmail(),
                // 'role' => $user->getRole(),
                'id' => $user->getId(),
            ]);

            return new JsonResponse([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'token' => $token
                ]
            ], 201);

        } catch (\Throwable $e) {
            $logger->error('User registration failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return new JsonResponse([
                 'status' => 'fail',
                 'message' => 'internal server error'
             ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, LoggerInterface $logger): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $schema = new LoginSchema();
            $schema->email = $data['email'] ?? null;
            $schema->password = $data['password'] ?? null;

            $errors = $this->validator->validate($schema);
            if (count($errors) > 0) {
                $firstError = $errors->get(0)->getMessage();
                return new JsonResponse(['status' => 'fail', 'message' => $firstError], 400);
            }

            $user = $this->userRepository->getUserByEmail($schema->email);


            if (!$user) {
                return new JsonResponse(['status' => 'fail', 'message' => 'User not found'], 404);
            }
            // تحويل الـ User إلى مصفوفة قابلة للـ JSON
            $userData = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'role' => $user->getRole()->value,
                'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
            ];

            $logger->info('get User :', $userData);


            if (!$this->passwordHasher->isPasswordValid($user, $schema->password)) {
                return new JsonResponse(['status' => 'fail', 'message' => 'Invalid password'], 401);
            }

            $token = $this->jwtManager->createFromPayload($user, [
                'email' => $user->getEmail(),
                // 'role' => $user->getRole(),
                'id' => $user->getId(),
            ]);

            $response = new JsonResponse([
            'status' => 'success',
            'message' => 'User login successful',
            'data' => [
                    'token' => $token
                            ]
                ], 200);
            $response->headers->setCookie(
                Cookie::create('BEARER', $token, new \DateTime('+1 hour'))
            );
            return $response;

        } catch (\Throwable $e) {
            $logger->error('User login failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return new JsonResponse([
                'status' => 'fail',
                'message' => 'internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function loginCheck(Request $request)
    {

    }
}
