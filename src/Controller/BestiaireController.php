<?php

namespace App\Controller;

use App\Repository\CreatureRepository;
use App\Service\MusicLibrary;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class BestiaireController extends AbstractController
{
    #[Route('/bestiaire', name: 'app_bestiaire', methods: ['GET'])]
    public function index(CreatureRepository $creatures, MusicLibrary $musicLibrary): Response
    {
        return $this->render('bestiaire/index.html.twig', [
            'creatures' => $creatures->findAllWithType(),
            'music' => $musicLibrary->getTrackPath('bestiaire'),
        ]);
    }
}
