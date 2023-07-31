<?php

namespace Roukmoute\SqidsBundle\ArgumentResolver;

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

        if ($argument->isVariadic()
            || !\is_string($value)
            || null === ($argument->getType())
        ) {
            return [];
        }

        return $this->sqids->decode($value);
    }
}
