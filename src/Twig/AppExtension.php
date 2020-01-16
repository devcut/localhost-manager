<?php

namespace App\Twig;

use Symfony\Component\Process\Process;
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

    public function version(): ?string
    {
        $process = new Process(['git', 'describe', '--tags']);
        $process->run();

        if (!$process->isSuccessful()) {
            return null;
        }

        return $process->getOutput();

    }
}