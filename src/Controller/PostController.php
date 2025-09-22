<?php

namespace App\Controller;

use App\Factory\PostFactory;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Validator\CreatePostSchema;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PostController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly PostFactory $postFactory, // injection ajoutée
    ) {
    }

    #[Route('/api/posts', name: 'create_post', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $schema = new CreatePostSchema();
        $schema->title = $data['title'] ?? null;

        $errors = $this->validator->validate($schema);

        if (count($errors) > 0) {
            $firstError = $errors->get(0)->getMessage();
            return new JsonResponse(['status' => 'fail', 'message' => $firstError], 400);
        }

        // get user
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['status' => 'fail', 'message' => 'User not authenticated'], 401);
        }

        // Crée un Post via la factory
        $post = PostFactory::make($user, $schema->title);

        // Persiste avec repository
        $this->postRepository->createPost($post);

        return new JsonResponse([
            'status' => 'success',
            'status' => 'post create success',

            'data' => [
                'id' => $post->getId(),
                'title' => $post->getTitle(),

            ],
        ]);
    }
}
