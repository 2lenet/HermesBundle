<?php

namespace Lle\HermesBundle\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ConsentTokenManager
{
    protected readonly string $secret;

    public function __construct(ParameterBagInterface $parameters)
    {
        /** @var string $secret */
        $secret = $parameters->get('lle_hermes.app_secret');
        $this->secret = $secret;
    }

    public function getToken(string $email): string
    {
        return md5($email . $this->secret);
    }

    public function isTokenValid(string $email, string $token): bool
    {
        return $token === $this->getToken($email);
    }

    public function getConfirmToken(string $email, bool $value): string
    {
        return md5($email . ($value ? '1' : '0') . $this->secret);
    }

    public function isConfirmTokenValid(string $email, bool $value, string $token): bool
    {
        return $token === $this->getConfirmToken($email, $value);
    }
}
