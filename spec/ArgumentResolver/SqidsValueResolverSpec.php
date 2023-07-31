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

    function it_fails_when_no_attribute_for_argument()
    {
        $request = new Request([], [], []);
        $argumentMetadata = new ArgumentMetadata('foo', Sqids::class, false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_fails_when_attribute_not_string()
    {
        $request = new Request([], [], ['foo' => ['bar']]);
        $argumentMetadata = new ArgumentMetadata('foo', Sqids::class, false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_fails_when_argument_lacks_type()
    {
        $request = new Request([], [], ['foo' => 'U9']);
        $argumentMetadata = new ArgumentMetadata('foo', null, false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_fails_when_argument_type_not_class()
    {
        $request = new Request([], [], ['foo' => 'U9']);
        $argumentMetadata = new ArgumentMetadata('foo', 'string', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_resolves_sqids_argument()
    {
        $request = new Request([], [], ['foo' => 'U9']);
        $argumentMetadata = new ArgumentMetadata('foo', Sqids::class, false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([1]);
    }
}
