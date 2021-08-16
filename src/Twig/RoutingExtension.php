<?php

namespace App\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RoutingExtension extends AbstractExtension
{
    /**
     * @var string
     */
    private $baseHost;

    /**
     * @var string
     */
    private $baseScheme;

    /**
     * @var UrlGeneratorInterface $generator
     */
    private $generator;

    /**
     * RoutingExtension constructor.
     * @param string $baseHost
     * @param string $baseScheme
     * @param UrlGeneratorInterface $generator
     */
    public function __construct(string $baseHost, string $baseScheme, UrlGeneratorInterface $generator)
    {
        $this->baseHost = $baseHost;
        $this->baseScheme = $baseScheme;
        $this->generator = $generator;
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('email_path', [$this, 'getEmailPath'])
        ];
    }

    public function getEmailPath($name, $parameters = [], $relative = false)
    {
        return $this->baseScheme . '://' . $this->baseHost . $this->generator->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}