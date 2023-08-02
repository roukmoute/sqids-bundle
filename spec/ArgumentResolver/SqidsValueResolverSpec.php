<?php

namespace spec\Roukmoute\SqidsBundle\ArgumentResolver;

use PhpSpec\ObjectBehavior;
use Roukmoute\SqidsBundle\ArgumentResolver\SqidsValueResolver;
use Sqids\Sqids;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    function it_fails_when_attribute_is_object()
    {
        $request = new Request([], [], ['foo' => new stdClass()]);
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
        $argumentMetadata = new ArgumentMetadata('foo', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([1]);
    }

    function it_fails_with_sqid_with_multiple_integers()
    {
        $request = new Request([], [], ['id' => 'XgbK']);
        $argumentMetadata = new ArgumentMetadata('id', Sqids::class, false, false, null);

        $this->shouldThrow(new NotFoundHttpException('The sqid for the "id" parameter is invalid.'))->during('resolve', [$request, $argumentMetadata]);
    }

    function it_fails_with_sqid_with_bad_value_for_id()
    {
        $request = new Request([], [], ['id' => 'ccc']);
        $argumentMetadata = new ArgumentMetadata('id', Sqids::class, false, false, null);

        $this->shouldThrow(new NotFoundHttpException('The sqid for the "id" parameter is invalid.'))->during('resolve', [$request, $argumentMetadata]);
    }

    function it_resolves_with_value_equals_0()
    {
        $request = new Request([], [], ['id' => 'bV']);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([0]);
    }
}
