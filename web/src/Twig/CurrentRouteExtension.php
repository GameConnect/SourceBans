<?php

namespace SourceBans\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CurrentRouteExtension extends AbstractExtension
{
    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_current_route', [$this, 'isCurrentRoute']),
        ];
    }

    public function isCurrentRoute(string $route): bool
    {
        $request = $this->requestStack->getMasterRequest();
        $currentRoute = $request->get('_route');

        return $route == $currentRoute;
    }
}
