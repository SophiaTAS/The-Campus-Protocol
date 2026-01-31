<?php

namespace App\Controller;

use App\Service\MusicLibrary;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(MusicLibrary $musicLibrary): Response
    {
        return $this->render('home/index.html.twig', [
            'music' => $musicLibrary->getTrackPath('main_menu'),
        ]);
    }
}
