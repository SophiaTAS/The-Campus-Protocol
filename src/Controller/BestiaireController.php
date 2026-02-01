<?php

namespace App\Controller;

use App\Repository\AreneRepository;
use App\Repository\CreatureRepository;
use App\Service\MusicLibrary;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class BestiaireController extends AbstractController
{
    #[Route('/bestiaire', name: 'app_bestiaire', methods: ['GET'])]
    public function index(CreatureRepository $creatures, AreneRepository $arenes, MusicLibrary $musicLibrary): Response
    {
        return $this->render('bestiaire/index.html.twig', [
            'creatures' => $creatures->findAllWithType(),
            'arenes' => $arenes->findAll(),
            'music' => $musicLibrary->getTrackPath('bestiaire'),
        ]);
    }
}
