<?php

declare(strict_types=1);

namespace DMF\ContentApi\Serializer;

interface SerializerInterface
{
    public function serialize(object $dto): string;
}
