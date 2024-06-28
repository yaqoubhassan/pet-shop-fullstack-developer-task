<?php

namespace App\Services;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Validator;
use Carbon\Carbon;

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

    public function createToken($userUuid)
    {
        $now = new \DateTimeImmutable();
        $token = $this->config->builder()
            ->issuedBy(config('jwt.issuer'))
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->withClaim('user_uuid', $userUuid)
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
