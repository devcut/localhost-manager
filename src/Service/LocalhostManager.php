<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class LocalhostManager
{
    private $bag;

    public function __construct(ParameterBagInterface $bag)
    {
        $this->bag = $bag;
    }

    public function getPath(): string
    {
        return $this->bag->get('kernel.project_dir') . '/config/localhost_manager.yaml';
    }

    public function createConfigFile()
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->getPath(), '');
    }

    public function getConfigFile(): array
    {
        return Yaml::parseFile($this->getPath());
    }

    public function setConfigFile(array $data)
    {
        file_put_contents($this->getPath(), '');
        $yaml = Yaml::dump($data);
        file_put_contents($this->getPath(), $yaml);
    }
}