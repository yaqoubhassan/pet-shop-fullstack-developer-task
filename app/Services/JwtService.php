<?php

namespace App\Services;

use Lcobucci\JWT\Validation\Validator;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Configuration;
use Carbon\Carbon;
use App\Models\User;

class JwtService
{
    private $config;

    public function __construct()
    {
        $this->config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file(config('jwt.keys.private')),
            InMemory::file(config('jwt.keys.public'))
        );
    }

    public function generateToken(User $user)
    {
        $now = new \DateTimeImmutable();

        $token = $this->config->builder()
            ->issuedBy(config('jwt.issuer'))
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->withClaim('user_uuid', $user->uuid)
            ->getToken($this->config->signer(), $this->config->signingKey());

        return $token->toString();
    }

    public function validateToken($token)
    {
        try {
            $token = $this->config->parser()->parse($token);
            assert($token instanceof Plain);

            $constraints = $this->config->validationConstraints();
            $this->config->validator()->assert($token, ...$constraints);

            return true;
        } catch (RequiredConstraintsViolated | \Exception $e) {
            return false;
        }
    }

    public function getUserUuidFromToken($token)
    {
        $token = $this->config->parser()->parse($token);
        assert($token instanceof Plain);

        return $token->claims()->get('user_uuid');
    }
}
