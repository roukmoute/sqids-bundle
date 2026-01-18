<?php

declare(strict_types=1);

namespace Roukmoute\SqidsBundle\Twig;

use Sqids\SqidsInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SqidsExtension extends AbstractExtension
{
    public function __construct(private SqidsInterface $sqids)
    {
    }

    /**
     * @return array<TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('sqids_encode', [$this, 'encode']),
            new TwigFilter('sqids_decode', [$this, 'decode']),
        ];
    }

    public function encode(int $number): string
    {
        return $this->sqids->encode([$number]);
    }

    /**
     * @return array<int, int>
     */
    public function decode(string $sqid): array
    {
        return $this->sqids->decode($sqid);
    }
}
