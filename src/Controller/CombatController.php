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
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

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
        $data = $this->buildCombatViewData($id, $requestStack, $creatures, $arenes, $statutRepo, $statutService, $musicLibrary);
        if (!$data) {
            return $this->redirectToRoute('app_combat');
        }

        if ($this->getParameter('kernel.debug') && $requestStack->getCurrentRequest()) {
            $requestStack->getCurrentRequest()->attributes->set('_disable_profiler', true);
        }

        return $this->render('combat/battle.html.twig', $data);
    }

    #[Route('/combat/{id}/state', name: 'app_combat_state', methods: ['GET'])]
    public function state(
        string $id,
        RequestStack $requestStack,
        CreatureRepository $creatures,
        AreneRepository $arenes,
        StatutEffetRepository $statutRepo,
        StatutEffetService $statutService,
        MusicLibrary $musicLibrary
    ): Response {
        $data = $this->buildCombatViewData($id, $requestStack, $creatures, $arenes, $statutRepo, $statutService, $musicLibrary);
        if (!$data) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if ($this->getParameter('kernel.debug') && $requestStack->getCurrentRequest()) {
            $requestStack->getCurrentRequest()->attributes->set('_disable_profiler', true);
        }

        return new Response($this->renderView('combat/_state.html.twig', $data));
    }

    #[Route('/combat/{id}/resultat', name: 'app_combat_result', methods: ['GET'])]
    public function result(
        string $id,
        RequestStack $requestStack,
        CreatureRepository $creatures,
        AreneRepository $arenes,
        StatutEffetRepository $statutRepo,
        StatutEffetService $statutService,
        MusicLibrary $musicLibrary
    ): Response {
        $data = $this->buildCombatViewData($id, $requestStack, $creatures, $arenes, $statutRepo, $statutService, $musicLibrary);
        if (!$data) {
            return $this->redirectToRoute('app_combat');
        }

        if (!$data['combatFini']) {
            return $this->redirectToRoute('app_combat_show', ['id' => $id]);
        }

        return $this->render('combat/result.html.twig', $data);
    }

    #[Route('/combat/{id}/attaque', name: 'app_combat_attack', methods: ['POST'])]
    public function attaque(
        string $id,
        Request $request,
        RequestStack $requestStack,
        CreatureRepository $creatures,
        AreneRepository $arenes,
        StatutEffetRepository $statutRepo,
        StatutEffetService $statutService,
        MusicLibrary $musicLibrary,
        CombatService $combatService,
        HubInterface $hub
    ): Response {
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
        }

        $data = $this->buildCombatViewData($id, $requestStack, $creatures, $arenes, $statutRepo, $statutService, $musicLibrary);
        if ($data) {
            $payload = $this->renderView('combat/_state.html.twig', $data);
            $hub->publish(new Update('combat/' . $id, $payload));
        }

        if ($request->isXmlHttpRequest()) {
            if ($this->getParameter('kernel.debug')) {
                $request->attributes->set('_disable_profiler', true);
            }
            return new Response($payload ?? '');
        }

        return $this->redirectToRoute('app_combat_show', ['id' => $id]);
    }

    #[Route('/combat/{id}/adversaire', name: 'app_combat_ai', methods: ['POST'])]
    public function adversaire(
        string $id,
        Request $request,
        RequestStack $requestStack,
        CreatureRepository $creatures,
        AreneRepository $arenes,
        StatutEffetRepository $statutRepo,
        StatutEffetService $statutService,
        MusicLibrary $musicLibrary,
        CombatService $combatService,
        HubInterface $hub
    ): Response {
        $combat = $combatService->getCombat($id);
        if (!$combat) {
            return $this->redirectToRoute('app_combat');
        }

        if (($combat['etat']['adversaire_pv'] ?? 0) > 0) {
            $combatService->executerTourAdversaire($id);
        }

        $data = $this->buildCombatViewData($id, $requestStack, $creatures, $arenes, $statutRepo, $statutService, $musicLibrary);
        if ($data) {
            $payload = $this->renderView('combat/_state.html.twig', $data);
            $hub->publish(new Update('combat/' . $id, $payload));
        }

        if ($request->isXmlHttpRequest()) {
            if ($this->getParameter('kernel.debug')) {
                $request->attributes->set('_disable_profiler', true);
            }
            return new Response($payload ?? '');
        }

        return $this->redirectToRoute('app_combat_show', ['id' => $id]);
    }

    /**
     * @param array<int, array<string, mixed>> $statuts
     * @param array<int, \App\Entity\StatutEffet> $statutById
     * @return array<int, array<string, mixed>>
     */
    private function mapperStatuts(array $statuts, array $statutById): array
    {
        $result = [];
        foreach ($statuts as $statutData) {
            if (!isset($statutData['id'])) {
                continue;
            }
            $statut = $statutById[$statutData['id']] ?? null;
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

    private function buildCombatViewData(
        string $id,
        RequestStack $requestStack,
        CreatureRepository $creatures,
        AreneRepository $arenes,
        StatutEffetRepository $statutRepo,
        StatutEffetService $statutService,
        MusicLibrary $musicLibrary
    ): ?array {
        $combat = $requestStack->getSession()->get("combat_$id");
        if (!$combat) {
            return null;
        }

        $creatureMap = $creatures->findManyWithAttaquesAndType([
            (int) $combat['joueur'],
            (int) $combat['adversaire'],
        ]);
        $joueur = $creatureMap[$combat['joueur']] ?? null;
        $adversaire = $creatureMap[$combat['adversaire']] ?? null;

        $arene = $arenes->find($combat['arene']);
        if (!$joueur || !$adversaire || !$arene) {
            return null;
        }

        $statsJoueur = $statutService->calculerStatsModifiees($combat, $joueur, 'joueur');
        $statsAdversaire = $statutService->calculerStatsModifiees($combat, $adversaire, 'adversaire');

        $statutIds = [];
        foreach (['joueur', 'adversaire'] as $camp) {
            foreach ($combat['etat']['statuts'][$camp] ?? [] as $statutData) {
                if (isset($statutData['id'])) {
                    $statutIds[] = (int) $statutData['id'];
                }
            }
        }
        $statutById = $statutRepo->findByIds(array_unique($statutIds));

        $statutsAffiches = [
            'joueur' => $this->mapperStatuts($combat['etat']['statuts']['joueur'] ?? [], $statutById),
            'adversaire' => $this->mapperStatuts($combat['etat']['statuts']['adversaire'] ?? [], $statutById),
        ];

        $pvJoueur = (int) ($combat['etat']['joueur_pv'] ?? 0);
        $pvAdversaire = (int) ($combat['etat']['adversaire_pv'] ?? 0);
        $matchNul = $pvJoueur <= 0 && $pvAdversaire <= 0;
        $victoire = !$matchNul && $pvAdversaire <= 0;
        $defaite = !$matchNul && $pvJoueur <= 0;
        $combatFini = $matchNul || $victoire || $defaite;

        $musicSlug = pathinfo((string) $arene->getImagePath(), PATHINFO_FILENAME);

        $data = [
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
            'combatFini' => $combatFini,
            'victoire' => $victoire,
            'defaite' => $defaite,
            'matchNul' => $matchNul,
            'resultUrl' => $this->generateUrl('app_combat_result', ['id' => $id]),
            'music' => $musicLibrary->getTrackPath($musicSlug) ?? $musicLibrary->getTrackPath('melancolie2'),
        ];

        if (isset($combat['cache']['joueur'])) {
            $data['joueurCache'] = $combat['cache']['joueur'];
        }
        if (isset($combat['cache']['adversaire'])) {
            $data['adversaireCache'] = $combat['cache']['adversaire'];
        }

        return $data;
    }
}
