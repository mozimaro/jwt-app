<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }
    /**
     * Persiste un Post déjà créé (objet) en base
     */
    public function createPost(Post $post): Post
    {
        $em = $this->getEntityManager();
        $em->persist($post);
        $em->flush();

        return $post;
    }

}
