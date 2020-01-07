<?php

namespace App\Controller;

use App\Form\ConfigurationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function index()
    {
        $folders = [];
        $finder = new Finder();
        $filesystem = new Filesystem();

        $localhostManager = __DIR__ . '/../../config/localhost_manager.yaml';
        $localhostManagerContent = Yaml::parseFile($localhostManager);

        $finder->in($localhostManagerContent['localhost_manager']['folder']);
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

        return $this->render('main/index.html.twig', [
            'folders' => $folders
        ]);
    }

    /**
     * @Route("/configuration", name="configuration")
     */
    public function configuration(Request $request)
    {
        $filesystem = new Filesystem();
        $form = $this->createForm(ConfigurationType::class);
        $localhostManager = __DIR__ . '/../../config/localhost_manager.yaml';

        if ($filesystem->exists($localhostManager)) {

            $localhostManagerContent = Yaml::parseFile($localhostManager);
            $form->get('folder')->setData($localhostManagerContent['localhost_manager']['folder']);
            $form->get('exception')->setData($localhostManagerContent['localhost_manager']['exception']);

        } else {
            $filesystem->dumpFile($localhostManager, '');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            file_put_contents($localhostManager, '');

            $data = [
                'localhost_manager' => [
                    'folder' => $form->getData()['folder'],
                    'exception' => $form->getData()['exception']
                ]
            ];

            $yaml = Yaml::dump($data);
            file_put_contents($localhostManager, $yaml);

            return $this->redirectToRoute('homepage');
        }

        return $this->render('main/configuration.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
