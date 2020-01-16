<?php

namespace App\Service;

use ComposerLockParser\ComposerInfo;
use DateTime;
use IntlDateFormatter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class LocalhostManager
{
    /**
     * @var ParameterBagInterface
     */
    protected $bag;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    public function __construct(ParameterBagInterface $bag, RequestStack $requestStack)
    {
        $this->bag = $bag;
        $this->requestStack = $requestStack;
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

            $composerInfo = new ComposerInfo($splFileInfo->getPathname() . '/composer.lock');
            $packages = $composerInfo->getPackages();

            foreach ($packages as $package) {
                if ($package->getName() === 'symfony/framework-bundle') {
                    $framework[] = 'Symfony ' . $package->getVersion();
                } else if ($package->getName() === 'symfony/symfony') {
                    $framework[] = 'Symfony ' . $package->getVersion();
                }
            }
        }

        if ($filesystem->exists($splFileInfo->getPathname() . '/wp-load.php')) {
            $framework[] = 'Wordpress';
        }

        return $framework;
    }

    /**
     * @param SplFileInfo $splFileInfo
     * @return string|null
     * Return git remote url of project
     */
    public function getGithubRemoteUrl(SplFileInfo $splFileInfo): ?string
    {
        $process = new Process(['git', 'config', '--get', 'remote.origin.url']);
        $process->setWorkingDirectory($splFileInfo);
        $process->run();

        if (!$process->isSuccessful()) {
            return null;
        }

        return 'https://github.com/' . str_replace('git@github.com:', '', $process->getOutput());
    }

    public function getGithubLatestCommit(SplFileInfo $splFileInfo): ?string
    {
        $process = new Process(['git', 'log', '-1', '--format=%cd']);
        $process->setWorkingDirectory($splFileInfo);
        $process->run();

        if (!$process->isSuccessful()) {
            return null;
        }

        $date = new DateTime($process->getOutput());

        $IntlDateFormatter = new IntlDateFormatter(
            $this->requestStack->getCurrentRequest()->getLocale(),
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE
        );

        return $IntlDateFormatter->format($date);
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
                    'git' => $this->getGithubRemoteUrl($iterator),
                    'favicon' => $this->getFavicon($iterator),
                    'modification' => $this->getGithubLatestCommit($iterator)
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