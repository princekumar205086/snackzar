<?php

namespace App\Modules\User\DTOs;

use App\Modules\Shared\DTOs\BaseDTO;

class LoginDTO extends BaseDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}
}
