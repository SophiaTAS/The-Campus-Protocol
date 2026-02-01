<?php

namespace App\Controller;

use App\Repository\AreneRepository;
use App\Repository\CreatureRepository;
use App\Repository\StatutEffetRepository;
use App\Service\CombatService;
use App\Service\MusicLibrary;
use App\Service\StatutEffetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
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

    #[Route('/combat/demarrer', name: 'app_combat_start', methods: ['POST'])]
    public function start(
        Request $request,
        CreatureRepository $creatures,
        AreneRepository $arenes,
        CombatService $combatService
    ): Response {
        $creatureId = (int) $request->request->get('creature_id', 0);
        $areneId = (int) $request->request->get('arene_id', 0);

        if ($creatureId <= 0 || $areneId <= 0) {
            return $this->redirectToRoute('app_combat');
        }

        $joueur = $creatures->find($creatureId);
        $arene = $arenes->find($areneId);

        if (!$joueur || !$arene) {
            return $this->redirectToRoute('app_combat');
        }

        $tous = $creatures->findAll();
        $adversaire = null;
        $pool = array_values(array_filter($tous, static fn($c) => $c->getId() !== $joueur->getId()));
        if ($pool) {
            $adversaire = $pool[array_rand($pool)];
        }

        if (!$adversaire && $tous) {
            $adversaire = $tous[0];
        }

        $combatId = uniqid('combat_', true);
        $combatService->initialiserCombat($combatId, $joueur, $adversaire, $arene->getId());

        return $this->redirectToRoute('app_combat_show', ['id' => $combatId]);
    }

    #[Route('/combat/{id}', name: 'app_combat_show', methods: ['GET'])]
    public function show(
        string $id,
        RequestStack $requestStack,
        CreatureRepository $creatures,
        AreneRepository $arenes,
        StatutEffetRepository $statutRepo,
        StatutEffetService $statutService,
        MusicLibrary $musicLibrary
    ): Response {
        $combat = $requestStack->getSession()->get("combat_$id");
        if (!$combat) {
            return $this->redirectToRoute('app_combat');
        }

        $joueur = $creatures->findOneWithAttaquesAndType($combat['joueur']);
        $adversaire = $creatures->findOneWithAttaquesAndType($combat['adversaire']);
        $arene = $arenes->find($combat['arene']);

        if (!$joueur || !$adversaire || !$arene) {
            return $this->redirectToRoute('app_combat');
        }

        $statsJoueur = $statutService->calculerStatsModifiees($combat, $joueur, 'joueur');
        $statsAdversaire = $statutService->calculerStatsModifiees($combat, $adversaire, 'adversaire');

        $statutsAffiches = [
            'joueur' => $this->mapperStatuts($combat['etat']['statuts']['joueur'] ?? [], $statutRepo),
            'adversaire' => $this->mapperStatuts($combat['etat']['statuts']['adversaire'] ?? [], $statutRepo),
        ];

        $musicSlug = pathinfo((string) $arene->getImagePath(), PATHINFO_FILENAME);

        return $this->render('combat/battle.html.twig', [
            'combatId' => $id,
            'combat' => $combat,
            'joueur' => $joueur,
            'adversaire' => $adversaire,
            'arene' => $arene,
            'attaqueJoueur' => $statsJoueur['attaque'],
            'defenseJoueur' => $statsJoueur['defense'],
            'attaqueAdv' => $statsAdversaire['attaque'],
            'defenseAdv' => $statsAdversaire['defense'],
            'statuts' => $statutsAffiches,
            'cEstMonTour' => ($combat['etat']['tour_en_cours'] ?? null) === 'joueur',
            'music' => $musicLibrary->getTrackPath($musicSlug) ?? $musicLibrary->getTrackPath('melancolie2'),
        ]);
    }

    #[Route('/combat/{id}/attaque', name: 'app_combat_attack', methods: ['POST'])]
    public function attaque(string $id, Request $request, CombatService $combatService): Response
    {
        $nomAttaque = (string) $request->request->get('attaque', '');
        $combat = $combatService->getCombat($id);
        if (!$combat) {
            return $this->redirectToRoute('app_combat');
        }

        if (($combat['etat']['tour_en_cours'] ?? null) !== 'joueur') {
            return $this->redirectToRoute('app_combat_show', ['id' => $id]);
        }

        if ($nomAttaque !== '') {
            $combatService->executerTour($id, $nomAttaque);
            $combat = $combatService->getCombat($id);
            if (($combat['etat']['adversaire_pv'] ?? 1) > 0) {
                $combatService->executerTourAdversaire($id);
            }
        }

        return $this->redirectToRoute('app_combat_show', ['id' => $id]);
    }

    private function mapperStatuts(array $statuts, StatutEffetRepository $repo): array
    {
        $result = [];
        foreach ($statuts as $statutData) {
            if (!isset($statutData['id'])) {
                continue;
            }
            $statut = $repo->find($statutData['id']);
            if (!$statut) {
                continue;
            }
            $result[] = [
                'nom' => $statut->getNom(),
                'icone' => $statut->getIcone(),
                'description' => $statut->getDescription(),
                'restant' => $statutData['restant'] ?? 0,
            ];
        }

        return $result;
    }
}
