<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('version', [$this, 'version']),
        ];
    }

    public function version(): string
    {
        $packageJson = @json_decode(file_get_contents(__DIR__ . '/../../package.json'));

        if ($packageJson) {
            return $packageJson->version;
        }

        return 'package.json not found';
    }
}