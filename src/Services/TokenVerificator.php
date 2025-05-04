<?php

namespace App\Services;

use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class TokenVerificator
{
    private TokenGeneratorInterface $tokenGenerator;

    public function __construct(TokenGeneratorInterface $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
    }

    public function generateToken(): string
    {
        return $this->tokenGenerator->generateToken();
    }
}