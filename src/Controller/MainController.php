<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $folders = [];
        $finder = new Finder();
        $filesystem = new Filesystem();

        $finder->in('/Users/devcut/PhpstormProjects');
        $dirs = $finder->directories()->depth(0);

        foreach($dirs->getIterator() as $key => $iterator) {
            $folders[] = [
                'name' => $iterator->getFilename(),
                'framework' => $filesystem->exists($iterator->getPathname() . '/symfony.lock') ? 'symfony.png' : null,
            ];
        }

        usort($folders, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $this->render('index.html.twig', [
            'folders' => $folders
        ]);
    }
}
