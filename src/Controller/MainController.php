<?php

namespace App\Controller;

use App\Form\ConfigurationType;
use App\Service\LocalhostManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(LocalhostManager $lm)
    {
        $folders = $lm->getFolderProject();

        return $this->render('main/index.html.twig', [
            'folders' => $folders,
            'extension' => $lm->getConfigFile()['extension']
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

            $form->get('folder')->setData($localhostManagerContent['folder']);
            $form->get('extension')->setData($localhostManagerContent['extension']);
            $form->get('exception')->setData($lm->getExceptionsFolder());

        } else {
            $lm->createConfigFile();
        }

        $form->handleRequest($request);

        // On submit set data to config file
        if ($form->isSubmitted() && $form->isValid()) {

            $data = [
                'localhost_manager' => [
                    'folder' => $form->getData()['folder'],
                    'extension' => $form->getData()['extension'],
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

    /**
     * @Route("/configuration/development-folder")
     */
    public function developmentFolder(Request $request)
    {
        $developmentFolder = $request->request->get('folder');
        $finder = new Finder();
        $folders = [];
        $index = 0;

        $finder->in($developmentFolder);
        $dirs = $finder->directories()->depth(0);

        foreach($dirs->getIterator() as $key => $iterator) {
            $index++;
            $folders[] = [
                'id' => $iterator->getFilename(),
                'text' => $iterator->getFilename()
            ];
        }

        $response = new Response(json_encode($folders));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}