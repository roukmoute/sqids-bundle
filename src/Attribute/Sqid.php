<?php

declare(strict_types=1);

namespace Roukmoute\SqidsBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class Sqid
{
    public function __construct(
        public readonly ?string $parameter = null,
    ) {
    }
}
