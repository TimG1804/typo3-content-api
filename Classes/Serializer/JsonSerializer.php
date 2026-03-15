<?php

declare(strict_types=1);

namespace DMF\ContentApi\Serializer;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class JsonSerializer implements SerializerInterface
{
    private readonly Serializer $serializer;

    public function __construct()
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $normalizers = [
            new ObjectNormalizer(propertyAccessor: $propertyAccessor),
        ];
        $encoders = [
            new JsonEncoder(),
        ];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function serialize(object $dto): string
    {
        return $this->serializer->serialize($dto, 'json');
    }
}
