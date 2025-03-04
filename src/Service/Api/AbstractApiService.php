<?php

namespace Lle\HermesBundle\Service\Api;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractApiService
{
    public function __construct(
        protected NormalizerInterface $normalizer,
    ) {
    }

    protected function normalize(array|object $data, array $groups): array
    {
        return (array)$this->normalizer->normalize($data, 'json', ['groups' => $groups]);
    }
}
