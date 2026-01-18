<?php

declare(strict_types=1);

namespace spec\Roukmoute\SqidsBundle\Twig;

use PhpSpec\ObjectBehavior;
use Roukmoute\SqidsBundle\Twig\SqidsExtension;
use Sqids\Sqids;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class SqidsExtensionSpec extends ObjectBehavior
{
    private Sqids $sqids;

    public function let()
    {
        $this->sqids = new Sqids();
        $this->beConstructedWith($this->sqids);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SqidsExtension::class);
    }

    public function it_encodes_in_twig_file()
    {
        $extension = new SqidsExtension($this->sqids);
        $twig = new Environment(
            new ArrayLoader(['template' => '{{ 1|sqids_encode }}']),
            ['cache' => false, 'optimizations' => 0]
        );
        $twig->addExtension($extension);

        expect($twig->render('template'))->toBe($this->sqids->encode([1]));
    }

    public function it_decodes_in_twig_file()
    {
        $encoded = $this->sqids->encode([1]);
        $extension = new SqidsExtension($this->sqids);
        $twig = new Environment(
            new ArrayLoader(['template' => "{{ '{$encoded}'|sqids_decode|first }}"]),
            ['cache' => false, 'optimizations' => 0]
        );
        $twig->addExtension($extension);

        expect($twig->render('template'))->toBe('1');
    }
}
