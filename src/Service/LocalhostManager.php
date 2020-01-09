<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
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
    public function getConfigFile(): ?array
    {
        return Yaml::parseFile($this->getPath())['localhost_manager'];
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
    public function getExceptionsFolder(): ?array
    {
        $exceptions = [];
        $filesystem = new Filesystem();

        if ($filesystem->exists($this->getPath())) {
            if (isset($this->getConfigFile()['exception'])) {
                foreach ($this->getConfigFile()['exception'] as $folder) {
                    $exceptions[$folder] = $folder;
                }
            }
        }

        return $exceptions;
    }

    /**
     * @param SplFileInfo $splFileInfo
     * @return array|null
     * Check framework used in project
     */
    public function checkFramework(SplFileInfo $splFileInfo): ?array
    {
        $framework = [];
        $filesystem = new Filesystem();

        if ($filesystem->exists($splFileInfo->getPathname() . '/symfony.lock')) {
            $framework[] = 'symfony.png';
        }

        if ($filesystem->exists($splFileInfo->getPathname() . '/wp-load.php')) {
            $framework[] = 'wordpress.png';
        }

        return $framework;
    }

    /**
     * @param SplFileInfo $splFileInfo
     * @return string|null
     * Return git url of project
     */
    public function getGithubInfo(SplFileInfo $splFileInfo): ?string
    {
        $filesystem = new Filesystem();

        if ($filesystem->exists($splFileInfo->getPathname() . '/.git/config')) {
            $handle = fopen($splFileInfo->getPathname() . '/.git/config', 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, 'url = git@github.com:') !== false) {
                        $git = str_replace('url = git@github.com:', '', $line);
                        $git = str_replace('.git', '', $git);
                        $git = preg_replace("/\t|\n/", "", $git);

                        return 'https://github.com/' . $git;
                    }
                }

                fclose($handle);
            }
        }

        return null;
    }

    /**
     * @param SplFileInfo $splFileInfo
     * @return string|null
     * Return path of favicon if it's found
     */
    public function getFavicon(SplFileInfo $splFileInfo): ?string
    {
        $filesystem = new Filesystem();

        if ($filesystem->exists($splFileInfo->getPathname() . '/favicon.png') || $filesystem->exists($splFileInfo->getPathname() . '/public/favicon.png')) {
            return '/favicon.png';
        } else if ($filesystem->exists($splFileInfo->getPathname() . '/public/images/favicon.png')) {
            return '/images/favicon.png';
        }

        return null;
    }

    /**
     * @return array
     * Return folders project without excluded folders
     */
    public function getFolderProject()
    {
        $folderProjects = [];
        $finder = new Finder();
        $localhostManagerContent = $this->getConfigFile();

        $finder->in($localhostManagerContent['folder']);
        $dirs = $finder->directories()->depth(0);

        foreach ($dirs->getIterator() as $key => $iterator) {
            if (!in_array($iterator->getFilename(), $localhostManagerContent['exception'])) {
                $folderProjects[] = [
                    'name' => $iterator->getFilename(),
                    'framework' => $this->checkFramework($iterator),
                    'git' => $this->getGithubInfo($iterator),
                    'favicon' => $this->getFavicon($iterator)
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