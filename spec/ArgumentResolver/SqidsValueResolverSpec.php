<?php

namespace spec\Roukmoute\SqidsBundle\ArgumentResolver;

use PhpSpec\ObjectBehavior;
use Roukmoute\SqidsBundle\ArgumentResolver\SqidsValueResolver;
use Sqids\Sqids;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SqidsValueResolverSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(new Sqids());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SqidsValueResolver::class);
    }

    function it_fails_to_resolve_non_variadic_argument()
    {
        $request = new Request([], [], ['foo' => 'U9']);
        $argumentMetadata = new ArgumentMetadata('foo', Sqids::class, true, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_resolves_sqids_argument()
    {
        $request = new Request([], [], ['foo' => 'U9']);
        $argumentMetadata = new ArgumentMetadata('foo', Sqids::class, false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([1]);
    }
}
