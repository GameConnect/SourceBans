<?php

namespace SourceBans\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class DefaultController
{
    /** @var EngineInterface */
    private $templating;

    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    public function index(): Response
    {
        return $this->templating->renderResponse('index.html.twig');
    }
}
