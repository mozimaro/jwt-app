<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use App\Type\UserRole;

class RegisterSchema
{
    #[Assert\NotBlank(message: "Username is required.")]
    #[Assert\Length(
        max: 50,
        maxMessage: "Username cannot be longer than {{ limit }} characters."
    )]
    public ?string $username = null;

    #[Assert\NotBlank(message: "Email is required.")]
    #[Assert\Email(message: "Email must be a valid email address.")]
    public ?string $email = null;

    #[Assert\NotBlank(message: "Password is required.")]
    #[Assert\Length(
        min: 6,
        minMessage: "Password must be at least {{ limit }} characters long."
    )]
    public ?string $password = null;

    #[Assert\Choice(
        choices: [UserRole::USER->value, UserRole::ADMIN->value, null],
        message: "Role must be 'user' or 'admin'."
    )]
    public ?string $role = null;
}

namespace App\Validator;

use Symfony\Component\Validator\Constraints as Assert;

class LoginSchema
{
    #[Assert\NotBlank(message: "Email is required.")]
    #[Assert\Email(message: "Email must be a valid email address.")]
    public ?string $email = null;

    #[Assert\NotBlank(message: "Password is required.")]
    #[Assert\Length(
        min: 6,
        minMessage: "Password must be at least {{ limit }} characters long."
    )]
    public ?string $password = null;
}
