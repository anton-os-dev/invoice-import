<?php

namespace App\Integrations;

class AuthResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $sessionId = null,
        public readonly ?string $error = null,
    ) {}
}
