<?php

namespace App\Factory;

use App\Entity\Post;
use App\Entity\User;

class PostFactory
{
    /**
     * Crée un objet Post mais ne le persiste pas
     */
    public static function make(User $user, string $title): Post
    {
        $post = new Post();
        $post->setOwner($user);
        $post->setTitle($title);

        return $post;
    }
}
