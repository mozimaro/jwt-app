<?php

namespace App\Factory;

use App\Entity\User;
use App\Type\UserRole;

class UserFactory
{
    public static function create(
        string $email,
        string $username,
        string $password,
        ?UserRole $role = null
    ): User {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $user = new User();
        $user->setEmail($email)
             ->setUsername($username)
             ->setPassword($hashedPassword)
             ->setRole($role ?? UserRole::USER);

        return $user;
    }
}
