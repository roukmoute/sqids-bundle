<?php

namespace spec\Roukmoute\SqidsBundle\ValueResolver;

use PhpSpec\ObjectBehavior;
use Roukmoute\SqidsBundle\Attribute\Sqid;
use Roukmoute\SqidsBundle\ValueResolver\SqidsValueResolver;
use Sqids\Sqids;
use stdClass;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SqidsValueResolverSpec extends ObjectBehavior
{
    private Sqids $sqids;

    function let()
    {
        $this->sqids = new Sqids();
        $this->beConstructedWith($this->sqids, false, false, Sqids::DEFAULT_ALPHABET);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SqidsValueResolver::class);
    }

    // Basic resolution tests (without auto_convert, need explicit attribute or prefix)

    function it_fails_when_no_sqid_source_available()
    {
        $request = new Request([], [], ['foo' => $this->sqids->encode([1])]);
        $argumentMetadata = new ArgumentMetadata('foo', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_fails_to_resolve_variadic_argument()
    {
        $request = new Request([], [], ['_sqid_foo' => $this->sqids->encode([1])]);
        $argumentMetadata = new ArgumentMetadata('foo', 'int', true, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    // Prefix support (_sqid_)

    function it_resolves_with_sqid_prefix()
    {
        $request = new Request([], [], ['_sqid_id' => $this->sqids->encode([42])]);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([42]);
    }

    function it_throws_when_prefix_sqid_is_invalid()
    {
        $request = new Request([], [], ['_sqid_id' => $this->sqids->encode([1, 2, 3])]);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $this->shouldThrow(\LogicException::class)->during('resolve', [$request, $argumentMetadata]);
    }

    // Alias support (sqid, id)

    function it_resolves_using_sqid_alias()
    {
        $request = new Request([], [], ['sqid' => $this->sqids->encode([42])]);
        $argumentMetadata = new ArgumentMetadata('foo', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([42]);
    }

    function it_resolves_using_id_alias()
    {
        $request = new Request([], [], ['id' => $this->sqids->encode([42])]);
        $argumentMetadata = new ArgumentMetadata('foo', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([42]);
    }

    function it_prevents_alias_reuse_for_multiple_arguments()
    {
        $request = new Request([], [], ['id' => $this->sqids->encode([42])]);
        $argumentMetadata1 = new ArgumentMetadata('foo', 'int', false, false, null);
        $argumentMetadata2 = new ArgumentMetadata('bar', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata1)->shouldReturn([42]);
        $this->resolve($request, $argumentMetadata2)->shouldReturn([]);
    }

    // Attribute support

    function it_resolves_with_sqid_attribute()
    {
        $request = new Request([], [], ['id' => $this->sqids->encode([42])]);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null, false, [new Sqid()]);

        $this->resolve($request, $argumentMetadata)->shouldReturn([42]);
    }

    function it_resolves_with_sqid_attribute_and_custom_parameter()
    {
        $request = new Request([], [], ['custom' => $this->sqids->encode([42])]);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null, false, [new Sqid(parameter: 'custom')]);

        $this->resolve($request, $argumentMetadata)->shouldReturn([42]);
    }

    function it_throws_logic_exception_when_decode_fails_with_attribute()
    {
        $request = new Request([], [], ['id' => $this->sqids->encode([1, 2, 3])]);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null, false, [new Sqid()]);

        $this->shouldThrow(\LogicException::class)->during('resolve', [$request, $argumentMetadata]);
    }

    // Auto convert mode

    function it_resolves_automatically_when_auto_convert_enabled()
    {
        $this->beConstructedWith($this->sqids, false, true, Sqids::DEFAULT_ALPHABET);

        $request = new Request([], [], ['id' => $this->sqids->encode([42])]);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([42]);
    }

    function it_skips_invalid_characters_when_auto_convert_enabled()
    {
        $this->beConstructedWith($this->sqids, false, true, Sqids::DEFAULT_ALPHABET);

        $request = new Request([], [], ['id' => 'invalid-chars!']);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    // Passthrough mode

    function it_resolves_with_passthrough_and_auto_convert_enabled(Request $request, ParameterBag $attributes)
    {
        $this->beConstructedWith($this->sqids, true, true, Sqids::DEFAULT_ALPHABET);

        $encoded = $this->sqids->encode([42]);
        $request->attributes = $attributes;
        $attributes->get('_sqid_id')->willReturn(null);
        $attributes->get('id')->willReturn($encoded);
        $attributes->has('sqids_prevent_alias')->willReturn(false);
        $attributes->has('sqid')->willReturn(false);
        $attributes->has('id')->willReturn(false);

        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $attributes->set('id', 42)->shouldBeCalled();
        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_sets_decoded_value_in_request_when_passthrough_enabled(Request $request, ParameterBag $attributes)
    {
        $this->beConstructedWith($this->sqids, true, false, Sqids::DEFAULT_ALPHABET);

        $encoded = $this->sqids->encode([42]);
        $request->attributes = $attributes;
        $attributes->get('_sqid_id')->willReturn($encoded);
        $attributes->has('sqids_prevent_alias')->willReturn(false);
        $attributes->has('sqid')->willReturn(false);
        $attributes->has('id')->willReturn(false);

        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $attributes->set('id', 42)->shouldBeCalled();
        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    // Edge cases

    function it_resolves_with_value_equals_0()
    {
        $request = new Request([], [], ['_sqid_id' => $this->sqids->encode([0])]);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([0]);
    }

    function it_skips_when_sqid_contains_invalid_alphabet_chars()
    {
        $request = new Request([], [], ['_sqid_id' => '!!!']);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_skips_non_string_alias_values()
    {
        $request = new Request([], [], ['id' => 123]);
        $argumentMetadata = new ArgumentMetadata('foo', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_skips_when_decode_returns_empty_array(\Sqids\SqidsInterface $mockSqids)
    {
        $this->beConstructedWith($mockSqids, false, true, Sqids::DEFAULT_ALPHABET);

        $mockSqids->decode('validlooking')->willReturn([]);

        $request = new Request([], [], ['id' => 'validlooking']);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null);

        $this->resolve($request, $argumentMetadata)->shouldReturn([]);
    }

    function it_throws_when_decode_returns_empty_array_with_attribute(\Sqids\SqidsInterface $mockSqids)
    {
        $this->beConstructedWith($mockSqids, false, false, Sqids::DEFAULT_ALPHABET);

        $mockSqids->decode('validlooking')->willReturn([]);

        $request = new Request([], [], ['id' => 'validlooking']);
        $argumentMetadata = new ArgumentMetadata('id', 'int', false, false, null, false, [new Sqid()]);

        $this->shouldThrow(\LogicException::class)->during('resolve', [$request, $argumentMetadata]);
    }
}
