<?php

namespace App\Controller;

use App\Repository\AreneRepository;
use App\Repository\CreatureRepository;
use App\Service\MusicLibrary;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CombatController extends AbstractController
{
    #[Route('/combat', name: 'app_combat', methods: ['GET'])]
    public function index(
        CreatureRepository $creatures,
        AreneRepository $arenes,
        MusicLibrary $musicLibrary
    ): Response {
        return $this->render('combat/index.html.twig', [
            'creatures' => $creatures->findAllWithType(),
            'arenes' => $arenes->findAll(),
            'music' => $musicLibrary->getTrackPath('melancolie2'),
        ]);
    }
}
