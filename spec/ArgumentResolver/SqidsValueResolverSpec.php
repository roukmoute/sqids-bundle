<?php

namespace spec\Roukmoute\SqidsBundle\ArgumentResolver;

use PhpSpec\ObjectBehavior;
use Roukmoute\SqidsBundle\ArgumentResolver\SqidsValueResolver;
use Sqids\Sqids;
use stdClass;
use Symfony\Component\HttpFoundation\ParameterBag;
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
        $argumentMetadata = new ArgumentMetadata('foo', 'int', true, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_fails_when_no_attribute_for_argument()
    {
        $request = new Request([], [], []);
        $argumentMetadata = new ArgumentMetadata('foo', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_fails_when_attribute_not_string()
    {
        $request = new Request([], [], ['foo' => ['bar']]);
        $argumentMetadata = new ArgumentMetadata('foo', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_fails_when_attribute_is_object()
    {
        $request = new Request([], [], ['foo' => new stdClass()]);
        $argumentMetadata = new ArgumentMetadata('foo', 'int', false, false, null);

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

    function it_resolves_sqids_int_argument()
    {
        $request = new Request([], [], ['foo' => 'U9']);
        $argumentMetadata = new ArgumentMetadata('foo', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([1]);
    }

    function it_resolves_sqids_object_argument(Request $request, ParameterBag $attributes)
    {
        $request->attributes = $attributes;
        $attributes->get('foo')->willReturn('U9');
        $argumentMetadata = new ArgumentMetadata('foo', stdClass::class, false, false, null);

        $attributes->set('foo', 1)->shouldBeCalled();
        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_fails_with_sqid_with_multiple_integers()
    {
        $request = new Request([], [], ['id' => 'XgbK']);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $this->shouldThrow(new NotFoundHttpException('The sqid for the "id" parameter is invalid.'))->during('resolve', [$request, $argumentMetadata]);
    }

    function it_fails_with_sqid_with_bad_value_for_id()
    {
        $request = new Request([], [], ['id' => 'ccc']);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $this->shouldThrow(new NotFoundHttpException('The sqid for the "id" parameter is invalid.'))->during('resolve', [$request, $argumentMetadata]);
    }

    function it_resolves_with_value_equals_0()
    {
        $request = new Request([], [], ['id' => 'bV']);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([0]);
    }

    public function it_fails_with_wrong_type_argument()
    {
        $request = new Request([], [], ['id' => 'bV']);
        $argumentMetadata = new ArgumentMetadata('id', 'string', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }
}
