<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $finder->in('/Users/devcut/PhpstormProjects');
        $dirs = $finder->directories()->depth(0);

        foreach($dirs->getIterator() as $iterator) {
            $folders[] = $iterator->getFilename();
        }

        return $this->render('index.html.twig', [
            'folders' => $folders
        ]);
    }
}
