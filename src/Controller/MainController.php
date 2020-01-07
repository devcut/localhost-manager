<?php

namespace App\Controller;

use App\Form\ConfigurationType;
use App\Service\LocalhostManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(LocalhostManager $lm)
    {
        $folders = [];
        $finder = new Finder();
        $filesystem = new Filesystem();
        $localhostManagerContent = $lm->getConfigFile();

        $finder->in($localhostManagerContent['localhost_manager']['folder']);
        $dirs = $finder->directories()->depth(0);

        foreach($dirs->getIterator() as $key => $iterator) {
            $folders[] = [
                'name' => $iterator->getFilename(),
                'framework' => $filesystem->exists($iterator->getPathname() . '/symfony.lock') ? 'symfony.png' : null,
            ];
        }

        // Sort folder by ASC name
        usort($folders, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $this->render('main/index.html.twig', [
            'folders' => $folders
        ]);
    }

    /**
     * @Route("/configuration", name="configuration")
     */
    public function configuration(Request $request, LocalhostManager $lm)
    {
        $filesystem = new Filesystem();
        $form = $this->createForm(ConfigurationType::class);

        // Check if config file exist
        if ($filesystem->exists($lm->getPath())) {

            $localhostManagerContent = $lm->getConfigFile();
            $form->get('folder')->setData($localhostManagerContent['localhost_manager']['folder']);
            $form->get('exception')->setData($localhostManagerContent['localhost_manager']['exception']);

        } else {
            $lm->createConfigFile();
        }

        $form->handleRequest($request);

        // On submit set data to config file
        if ($form->isSubmitted() && $form->isValid()) {

            $data = [
                'localhost_manager' => [
                    'folder' => $form->getData()['folder'],
                    'exception' => $form->getData()['exception']
                ]
            ];

            $lm->setConfigFile($data);

            return $this->redirectToRoute('homepage');
        }

        return $this->render('main/configuration.html.twig', [
            'form' => $form->createView()
        ]);
    }
}