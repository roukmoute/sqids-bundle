<?php

namespace Roukmoute\SqidsBundle\ArgumentResolver;

use InvalidArgumentException;
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

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $value = $request->attributes->get($argument->getName());
        $type = $argument->getType();

        if ($argument->isVariadic()
            || !\is_string($value)
            || \is_object($value)
            || $type === null
            || !is_a($type, Sqids::class, true)
        ) {
            return [];
        }

        try {
            return $this->decode($value);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException(sprintf('The sqid for the "%s" parameter is invalid.', $argument->getName()), $e);
        }
    }

    private function decode(string $value): array
    {
        $decodedValues = $this->sqids->decode($value);

        if (count($decodedValues) > 1) {
            throw new InvalidArgumentException('Only one value expected');
        }

        $decodedValue = $decodedValues[0];

        if ($decodedValue === 0 && $this->sqids->encode($decodedValues) !== $value) {
            throw new InvalidArgumentException('Invalid value');
        }

        return [$decodedValue];
    }
}
