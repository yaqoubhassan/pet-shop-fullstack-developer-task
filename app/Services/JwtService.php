<?php

namespace App\Services;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;

class JwtService
{
    private $config;

    public function __construct()
    {
        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            Key\InMemory::plainText(env('JWT_SECRET'))
        );
    }

    public function createToken($userId): Plain
    {
        return $this->config->builder()
        ->issuedBy(env('APP_URL'))
        ->permittedFor(env('APP_URL'))
        ->identifiedBy(bin2hex(random_bytes(16)), true)
        ->issuedAt(new \DateTimeImmutable())
        ->canOnlyBeUsedAfter(new \DateTimeImmutable())
        ->expiresAt((new \DateTimeImmutable())->modify('+1 hour'))
        ->withClaim('uid', $userId)
        ->getToken($this->config->signer(), $this->config->signingKey());
    }

    public function validateToken(string $token): bool
    {
        try {
            $jwt = $this->config->parser()->parse($token);
            $constraints = $this->config->validationConstraints();
            $this->config->validator()->assert($jwt, ...$constraints);
            return true;
        } catch (RequiredConstraintsViolated $e) {
            return false;
        }
    }

    public function parseToken(string $token): ?Plain
    {
        try {
            return $this->config->parser()->parse($token);
        } catch (\Exception $e) {
            return null;
        }
    }
}
