<?php

namespace App\Service;

use App\Entity\Attaque;
use App\Entity\Creature;
use App\Repository\CreatureRepository;
use App\Repository\TypeMultiplicateurRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CombatService
{
    private SessionInterface $session;

    public function __construct(
        private RequestStack $requestStack,
        private CreatureRepository $creatureRepository,
        private TypeMultiplicateurRepository $multiplicateurRepository,
        private StatutEffetService $statutEffetService,
    ) {
        $this->session = $this->requestStack->getSession();
    }

    public function initialiserCombat(string $combatId, Creature $joueur, Creature $adversaire, int $areneId): void
    {
        $combat = [
            'joueur' => $joueur->getId(),
            'adversaire' => $adversaire->getId(),
            'arene' => $areneId,
            'etat' => [
                'joueur_pv' => $joueur->getPvMax(),
                'adversaire_pv' => $adversaire->getPvMax(),
                'tour_en_cours' => 'joueur',
                'camp_actif' => null,
                'log' => [
                    "⚔️ Duel lancé entre {$joueur->getNom()} et {$adversaire->getNom()} !",
                ],
                'statuts' => [
                    'joueur' => [],
                    'adversaire' => [],
                ],
                'bloque' => [
                    'joueur' => false,
                    'adversaire' => false,
                ],
                'effet_visuel' => null,
            ],
        ];

        $this->session->set("combat_$combatId", $combat);
    }

    public function getCombat(string $combatId): ?array
    {
        $combat = $this->session->get("combat_$combatId");
        return is_array($combat) ? $combat : null;
    }

    public function executerTour(string $combatId, string $nomAttaque): void
    {
        $combat = $this->session->get("combat_$combatId") ?? [];
        if (!isset($combat['joueur'], $combat['adversaire'])) {
            return;
        }

        $joueur = $this->creatureRepository->find($combat['joueur']);
        $adversaire = $this->creatureRepository->find($combat['adversaire']);
        if (!$joueur || !$adversaire) {
            return;
        }

        $combat['etat']['camp_actif'] = 'joueur';
        $combat['etat']['effet_visuel'] = null;
        $combat['etat']['bloque']['joueur'] = false;

        $this->statutEffetService->traiterStatuts($combat, 'joueur', $joueur);
        if ($this->statutEffetService->estBloque($combat, 'joueur')) {
            $combat['etat']['log'][] = "🧑 {$joueur->getNom()} ne peut pas agir ce tour.";
            $combat['etat']['tour_en_cours'] = 'adversaire';
            $this->session->set("combat_$combatId", $combat);
            return;
        }

        $attaque = $this->trouverAttaque($joueur, $nomAttaque);
        if (!$attaque) {
            $combat['etat']['log'][] = "❌ Attaque inconnue.";
            $this->session->set("combat_$combatId", $combat);
            return;
        }

        $this->executerAttaque($joueur, $adversaire, $attaque, $combat, 'joueur', 'adversaire');

        if ($combat['etat']['adversaire_pv'] <= 0) {
            $combat['etat']['adversaire_pv'] = 0;
            $combat['etat']['log'][] = "🏆 {$joueur->getNom()} a vaincu {$adversaire->getNom()} !";
        }

        $combat['etat']['tour_en_cours'] = 'adversaire';
        $this->session->set("combat_$combatId", $combat);
    }

    public function executerTourAdversaire(string $combatId): void
    {
        $combat = $this->session->get("combat_$combatId") ?? [];
        if (!isset($combat['joueur'], $combat['adversaire'])) {
            return;
        }

        $joueur = $this->creatureRepository->find($combat['joueur']);
        $adversaire = $this->creatureRepository->find($combat['adversaire']);
        if (!$joueur || !$adversaire) {
            return;
        }

        $combat['etat']['camp_actif'] = 'adversaire';
        $combat['etat']['effet_visuel'] = null;
        $combat['etat']['bloque']['adversaire'] = false;

        $this->statutEffetService->traiterStatuts($combat, 'adversaire', $adversaire);
        if ($this->statutEffetService->estBloque($combat, 'adversaire')) {
            $combat['etat']['log'][] = "🤖 {$adversaire->getNom()} est paralysé et ne peut pas agir.";
            $combat['etat']['tour_en_cours'] = 'joueur';
            $this->session->set("combat_$combatId", $combat);
            return;
        }

        try {
            $attaque = $this->choisirAttaqueAleatoire($adversaire);
            $this->executerAttaque($adversaire, $joueur, $attaque, $combat, 'adversaire', 'joueur');
        } catch (\RuntimeException $e) {
            $combat['etat']['log'][] = "🤖 {$adversaire->getNom()} n'a aucune attaque disponible.";
        }

        if ($combat['etat']['joueur_pv'] <= 0) {
            $combat['etat']['joueur_pv'] = 0;
            $combat['etat']['log'][] = "💀 {$adversaire->getNom()} met K.O. {$joueur->getNom()} !";
        }

        $combat['etat']['tour_en_cours'] = 'joueur';
        $this->session->set("combat_$combatId", $combat);
    }

    private function executerAttaque(
        Creature $attaquant,
        Creature $cible,
        Attaque $attaque,
        array &$combat,
        string $campAtt,
        string $campCible
    ): void {
        $degats = $this->calculerDegats($combat, $attaquant, $cible, $attaque, $campAtt, $campCible);
        $combat['etat'][$campCible . '_pv'] -= $degats;
        $combat['etat']['log'][] = ($campAtt === 'joueur' ? '🧑' : '🤖') . " {$attaquant->getNom()} utilise {$attaque->getNom()} et inflige {$degats} dégâts.";

        $mult = $this->getMultiplicateurFromDB($attaquant, $cible);
        if ($mult !== 1.0) {
            $combat['etat']['effet_visuel'] = $mult > 1 ? 'x2' : 'x0.5';
            $combat['etat']['log'][] = $mult > 1
                ? "💥 C'est super efficace contre {$cible->getNom()} !"
                : "😐 Ce n'est pas très efficace contre {$cible->getNom()}...";
        }

        $statut = $attaque->getStatutEffet();
        if ($statut) {
            $chance = $attaque->getChance();
            $roll = random_int(0, 100) / 100;
            if ($roll <= $chance) {
                $campEffet = $statut->getCible() === 'lanceur' ? $campAtt : $campCible;
                $cibleNom = $statut->getCible() === 'lanceur' ? $attaquant->getNom() : $cible->getNom();
                $this->statutEffetService->appliquerStatut($combat, $campEffet, $statut->getId());
                $combat['etat']['log'][] = "{$statut->getIcone()} {$cibleNom} est affecté par {$statut->getNom()} !";
            }
        }
    }

    private function trouverAttaque(Creature $creature, string $nom): ?Attaque
    {
        foreach ($creature->getAttaques() as $attaque) {
            if ($attaque->getNom() === $nom) {
                return $attaque;
            }
        }

        return null;
    }

    private function choisirAttaqueAleatoire(Creature $creature): Attaque
    {
        $attaques = $creature->getAttaques()->toArray();
        if (!$attaques) {
            throw new \RuntimeException('Aucune attaque disponible pour cette créature.');
        }
        return $attaques[array_rand($attaques)];
    }

    private function calculerDegats(
        array $combat,
        Creature $attaquant,
        Creature $cible,
        Attaque $attaque,
        string $campAtt,
        string $campCible
    ): int {
        $statsAtt = $this->statutEffetService->calculerStatsModifiees($combat, $attaquant, $campAtt);
        $statsCib = $this->statutEffetService->calculerStatsModifiees($combat, $cible, $campCible);

        $attaqueBase = $statsAtt['attaque'];
        $defenseBase = $statsCib['defense'];
        $ratio = $attaque->getDegats() / 100;
        $mult = $this->getMultiplicateurFromDB($attaquant, $cible);

        return max(0, (int) round(($attaqueBase - $defenseBase / 2) * $ratio * $mult));
    }

    private function getMultiplicateurFromDB(Creature $attaquant, Creature $cible): float
    {
        $source = $attaquant->getType();
        $cibleType = $cible->getType();
        if (!$source || !$cibleType) {
            return 1.0;
        }

        return $this->multiplicateurRepository
            ->findOneBy(['typeSource' => $source, 'typeCible' => $cibleType])?->getMultiplicateur() ?? 1.0;
    }
}
