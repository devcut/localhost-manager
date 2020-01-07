<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class LocalhostManager
{
    private $bag;

    public function __construct(ParameterBagInterface $bag)
    {
        $this->bag = $bag;
    }

    /**
     * @return string
     * Get path of configuration file
     */
    public function getPath(): string
    {
        return $this->bag->get('kernel.project_dir') . '/config/localhost_manager.yaml';
    }

    /**
     * Create config file if not exists with the good path
     */
    public function createConfigFile(): void
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->getPath(), '');
    }

    /**
     * @return array
     * Parse content of configuration file
     */
    public function getConfigFile(): array
    {
        return Yaml::parseFile($this->getPath());
    }

    /**
     * @param array $data
     * Populate configuration file
     */
    public function setConfigFile(array $data): void
    {
        file_put_contents($this->getPath(), '');
        $yaml = Yaml::dump($data);
        file_put_contents($this->getPath(), $yaml);
    }

    /**
     * @return array
     * Return array of exception folder
     */
    public function getExceptionsFolder(): array
    {
        $exceptions = [];

        if (isset($this->getConfigFile()['localhost_manager']['exception'])) {
            foreach ($this->getConfigFile()['localhost_manager']['exception'] as $folder) {
                $exceptions[$folder] = $folder;
            }
        }

        return $exceptions;
    }

    /**
     * @return array
     * Return folders project without excluded folders
     */
    public function getFolderProject()
    {
        $folderProjects = [];
        $finder = new Finder();
        $filesystem = new Filesystem();
        $localhostManagerContent = $this->getConfigFile();

        $finder->in($localhostManagerContent['localhost_manager']['folder']);
        $dirs = $finder->directories()->depth(0);

        foreach($dirs->getIterator() as $key => $iterator) {
            if (!in_array($iterator->getFilename(), $localhostManagerContent['localhost_manager']['exception'])) {
                $folderProjects[] = [
                    'name' => $iterator->getFilename(),
                    'framework' => $filesystem->exists($iterator->getPathname() . '/symfony.lock') ? 'symfony.png' : null,
                ];
            }
        }

        // Sort folder by ASC name
        usort($folderProjects, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $folderProjects;
    }
}