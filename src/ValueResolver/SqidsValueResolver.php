<?php

declare(strict_types=1);

namespace Roukmoute\SqidsBundle\ValueResolver;

use Roukmoute\SqidsBundle\Attribute\Sqid;
use Sqids\Sqids;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SqidsValueResolver implements ValueResolverInterface
{
    public function __construct(private Sqids $sqids)
    {
    }

    /**
     * @return iterable<int>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $sqidAttribute = $this->getSqidAttribute($argument);
        $name = $argument->getName();
        $routeParameter = $sqidAttribute?->parameter ?? $name;
        $value = $request->attributes->get($routeParameter);

        if ($argument->isVariadic() || !\is_string($value)) {
            return [];
        }

        $hasAttribute = $sqidAttribute !== null;

        if (!$hasAttribute && !$this->isValidType($argument->getType())) {
            return [];
        }

        return $this->decodeAndResolve($request, $argument, $value, $hasAttribute);
    }

    private function isValidType(?string $type): bool
    {
        return $type === 'int' || ($type !== null && class_exists($type));
    }

    /**
     * @return iterable<int>
     */
    private function decodeAndResolve(Request $request, ArgumentMetadata $argument, string $value, bool $hasAttribute): iterable
    {
        $name = $argument->getName();
        $type = $argument->getType();
        $class = $type !== null && class_exists($type) ? $type : null;

        try {
            $decode = $this->decode($value);

            if ($class) {
                $request->attributes->set($name, $decode[0]);

                return [];
            }

            return $decode;
        } catch (\InvalidArgumentException $e) {
            return $this->handleDecodeException($e, $name, $hasAttribute);
        }
    }

    /**
     * @return never
     */
    private function handleDecodeException(\InvalidArgumentException $e, string $name, bool $hasAttribute): iterable
    {
        if ($hasAttribute) {
            throw new \LogicException(sprintf('Unable to decode parameter "%s".', $name), 0, $e);
        }
        throw new NotFoundHttpException(sprintf('The sqid for the "%s" parameter is invalid.', $name), $e);
    }

    private function getSqidAttribute(ArgumentMetadata $argument): ?Sqid
    {
        /** @var Sqid[] $attributes */
        $attributes = $argument->getAttributes(Sqid::class, ArgumentMetadata::IS_INSTANCEOF);

        return $attributes[0] ?? null;
    }

    /**
     * @return array<int, int>
     */
    private function decode(string $value): array
    {
        $decodedValues = $this->sqids->decode($value);

        if (count($decodedValues) > 1) {
            throw new \InvalidArgumentException('Only one value expected');
        }

        $decodedValue = $decodedValues[0];

        if ($decodedValue === 0 && $this->sqids->encode($decodedValues) !== $value) {
            throw new \InvalidArgumentException('Invalid value');
        }

        return [$decodedValue];
    }
}
