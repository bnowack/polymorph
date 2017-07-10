<?php

namespace src\Polymorph\Application;

use PhpSpec\ObjectBehavior;
use Polymorph\Application\Application as PolymorphApplication;
use Polymorph\Config\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use fixture\Application\DummyProvider;
use PHPUnit\Framework\Assert;

class ApplicationSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(PolymorphApplication::class);
    }

    public function it_provides_a_config_service()
    {
        $this['config']->shouldHaveType(Config::class);
    }

    public function it_has_a_config_trait()
    {
        $this['config']->set('foo', 'bar');
        $this->config('foo')->shouldBe('bar');
    }

    public function it_detects_a_partial_request(RequestStack $requestStack)
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

    public function it_initializes_custom_service_providers()
    {
        $this['config']->set('serviceProviders', [
            'dummy' => 'fixture\\Application\\DummyProvider'
        ]);

        try {
            $this['dummy'];
        } catch (\Exception $exception) {
        }

        $this->boot();
        $this['dummy']->shouldHaveType(DummyProvider::class);
    }

    public function it_detects_a_root_base(Request $request)
    {
        $this['config']->set('base', '/');
        $request->getPathInfo()->willReturn('/foo');
        $this->boot($request);
        $this->base->shouldBe('/');
    }

    public function it_detects_a_directory_base(Request $request)
    {
        $this['config']->set('base', '/foo/');
        $request->getPathInfo()->willReturn('/foo/bar');
        $this->boot($request);
        $this->base->shouldBe('/foo/');
    }

    public function it_detects_an_optional_base(Request $request)
    {
        $this['config']->set('base', ['/base1/', '/base2/']);
        $request->getPathInfo()->willReturn('/base2/bar');
        $this->boot($request);
        $this->base->shouldBe('/base2/');
    }

    public function it_initializes_routes()
    {
        $this['config']->set('routes', (object)[
            "/manifest.json" => (object)[
                "template" => "Polymorph/Application/templates/manifest.json.twig",
                "contentType" => "application/json"
            ]
        ]);

        $this->boot();
        $this->flush();
        $this['routes']->count()->shouldBe(1);
    }
}
