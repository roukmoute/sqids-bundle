<?php

declare(strict_types=1);

namespace Roukmoute\SqidsBundle\ValueResolver;

use Roukmoute\SqidsBundle\Attribute\Sqid;
use Sqids\SqidsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SqidsValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly SqidsInterface $sqids,
        private readonly bool $passthrough,
        private readonly bool $autoConvert,
        private readonly string $alphabet,
    ) {
    }

    /**
     * @return iterable<int>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $name = $argument->getName();
        $sqidAttribute = $this->getSqidAttribute($argument);
        $routeParameter = $sqidAttribute?->parameter ?? $name;
        [$sqid, $isExplicit] = $this->getSqid($request, $routeParameter, $sqidAttribute !== null);

        if ($this->isSkippable($sqid)) {
            return [];
        }

        $decoded = $this->sqids->decode($sqid);

        if ($this->hasSqidDecoded($decoded)) {
            /** @var int $decodedValue */
            $decodedValue = reset($decoded);

            if ($this->passthrough) {
                $request->attributes->set($name, $decodedValue);

                return [];
            }

            return [$decodedValue];
        }

        if ($isExplicit) {
            throw new \LogicException(sprintf('Unable to decode parameter "%s".', $name));
        }

        return [];
    }

    private function getSqidAttribute(ArgumentMetadata $argument): ?Sqid
    {
        /** @var Sqid[] $attributes */
        $attributes = $argument->getAttributes(Sqid::class, ArgumentMetadata::IS_INSTANCEOF);

        return $attributes[0] ?? null;
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private function getSqid(Request $request, string $name, bool $hasSqidAttribute): array
    {
        if ($name === '') {
            return ['', false];
        }

        $sqid = $request->attributes->get('_sqid_' . $name);
        if (isset($sqid) && \is_string($sqid)) {
            return [$sqid, true];
        }

        if ($this->autoConvert || $hasSqidAttribute) {
            $sqid = $request->attributes->get($name);
            if (\is_string($sqid)) {
                return [$sqid, $hasSqidAttribute];
            }
        }

        $sqid = $this->getSqidFromAliases($request);
        if ($sqid !== '') {
            return [$sqid, true];
        }

        return ['', false];
    }

    private function getSqidFromAliases(Request $request): string
    {
        $sqid = '';

        if (!$request->attributes->has('sqids_prevent_alias')) {
            foreach (['sqid', 'id'] as $alias) {
                if ($request->attributes->has($alias)) {
                    $aliasAttribute = $request->attributes->get($alias);
                    if (!\is_string($aliasAttribute)) {
                        continue;
                    }
                    $sqid = $aliasAttribute;
                    $request->attributes->set('sqids_prevent_alias', true);
                    break;
                }
            }
        }

        return $sqid;
    }

    private function isSkippable(string $sqid): bool
    {
        return $sqid === '' || !$this->allCharsAreInAlphabet($sqid);
    }

    private function allCharsAreInAlphabet(string $sqid): bool
    {
        return (bool) preg_match(sprintf('{^[%s]+$}', preg_quote($this->alphabet, '{')), $sqid);
    }

    /**
     * @param array<int, int> $decoded
     */
    private function hasSqidDecoded(array $decoded): bool
    {
        return \is_int(reset($decoded));
    }
}
