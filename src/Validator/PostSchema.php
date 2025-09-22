<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints as Assert;

class CreatePostSchema
{
    #[Assert\NotBlank(message: "title is required.")]
    #[Assert\Length(
        min: 3,
        minMessage: "title must be at least {{ limit }} characters long."
    )]
    public ?string $title = null;
}
