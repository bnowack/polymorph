<?php

namespace src\Polymorph\Application;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ApplicationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Polymorph\Application\Application');
    }

    function it_provides_a_config_service()
    {
        $this['config']->shouldHaveType('Polymorph\Config\Config');
    }

    function it_has_a_config_trait()
    {
        $this['config']->set('foo', 'bar');
        $this->config('foo')->shouldBe('bar');
    }

    function it_detects_a_partial_request(RequestStack $requestStack)
    {
        $this['request_stack'] = $requestStack;

        $request = new Request();
        $requestStack->getCurrentRequest()->willReturn($request);
        $this->isPartialRequest()->shouldReturn(false);

        $request = new Request(['partials' => 'foo']);
        $requestStack->getCurrentRequest()->willReturn($request);
        $this->isPartialRequest()->shouldReturn(false);

        $request = new Request(['partials' => 'true']);
        $requestStack->getCurrentRequest()->willReturn($request);
        $this->isPartialRequest()->shouldReturn(true);

    }
}
