<?php

namespace App\Service;

use App\Entity\Creature;
use App\Repository\StatutEffetRepository;

class StatutEffetService
{
    public function __construct(
        private StatutEffetRepository $statutEffetRepository,
    ) {}

    public function appliquerStatut(array &$combat, string $camp, int $statutId): void
    {
        if (!isset($combat['etat']['statuts'][$camp])) {
            $combat['etat']['statuts'][$camp] = [];
        }

        foreach ($combat['etat']['statuts'][$camp] as &$effet) {
            if (($effet['id'] ?? null) === $statutId) {
                $statut = $this->statutEffetRepository->find($statutId);
                if ($statut) {
                    $effet['restant'] = $statut->getDuree();
                }
                unset($effet);
                return;
            }
        }
        unset($effet);

        $statut = $this->statutEffetRepository->find($statutId);
        if (!$statut) {
            return;
        }

        $combat['etat']['statuts'][$camp][] = [
            'id' => $statutId,
            'restant' => $statut->getDuree(),
        ];
    }

    public function traiterStatuts(array &$combat, string $camp, Creature $creature): void
    {
        $statuts = $combat['etat']['statuts'][$camp] ?? [];
        $nouveauxStatuts = [];
        $logPrefix = $camp === 'joueur' ? '🧑' : '🤖';

        foreach ($statuts as $statutData) {
            if (!isset($statutData['id'])) {
                $nouveauxStatuts[] = $statutData;
                continue;
            }

            $statut = $this->statutEffetRepository->find($statutData['id']);
            if (!$statut) {
                continue;
            }

            $icone = $statut->getIcone();
            $nom = $statut->getNom();
            $type = $statut->getTypeEffet();
            $restant = (int) ($statutData['restant'] ?? 0);

            if ($restant > 0) {
                if ($type === 'degats_tour') {
                    $combat['etat'][$camp . '_pv'] -= 10;
                    $combat['etat']['log'][] = "$icone $logPrefix subit 10 PV à cause de $nom !";
                }

                if ($type === 'paralyse_logicielle') {
                    $combat['etat']['bloque'][$camp] = true;
                    $combat['etat']['log'][] = "$icone $logPrefix est bloqué par $nom ce tour-ci !";
                }

                if ($type === 'regen') {
                    $gain = 5;
                    $combat['etat'][$camp . '_pv'] += $gain;
                    $combat['etat']['log'][] = "$icone $logPrefix régénère $gain PV grâce à $nom !";
                    $combat['etat'][$camp . '_pv'] = min($combat['etat'][$camp . '_pv'], $creature->getPvMax());
                }
            }

            if (($combat['etat']['camp_actif'] ?? null) === $camp) {
                $restant--;
            }

            if ($restant > 0) {
                $nouveauxStatuts[] = ['id' => $statutData['id'], 'restant' => $restant];
            } else {
                $combat['etat']['log'][] = "$icone Le statut $nom s'est dissipé.";
            }
        }

        $combat['etat']['statuts'][$camp] = $nouveauxStatuts;
    }

    public function estBloque(array $combat, string $camp): bool
    {
        foreach ($combat['etat']['statuts'][$camp] ?? [] as $statutData) {
            if (!isset($statutData['id'])) {
                continue;
            }
            $statut = $this->statutEffetRepository->find($statutData['id']);
            if ($statut && $statut->getTypeEffet() === 'paralyse_logicielle' && ($statutData['restant'] ?? 0) > 0) {
                return true;
            }
        }

        return false;
    }

    public function calculerStatsModifiees(array $combat, Creature $creature, string $camp): array
    {
        $attaque = $creature->getAttaque();
        $defense = $creature->getDefens();

        foreach ($combat['etat']['statuts'][$camp] ?? [] as $effetData) {
            if (!isset($effetData['id'])) {
                continue;
            }
            $effet = $this->statutEffetRepository->find($effetData['id']);
            if (!$effet) {
                continue;
            }

            $type = $effet->getTypeEffet();
            $cibleStat = $effet->getCibleStat();

            if ($type === 'boost') {
                if ($cibleStat === 'attaque') {
                    $attaque = (int) round($attaque * 1.2);
                }
                if ($cibleStat === 'defense') {
                    $defense = (int) round($defense * 1.3);
                }
            }

            if ($type === 'malus') {
                if ($cibleStat === 'attaque') {
                    $attaque = (int) round($attaque * 0.9);
                }
                if ($cibleStat === 'defense') {
                    $defense = (int) round($defense * 0.9);
                }
            }
        }

        return [
            'attaque' => $attaque,
            'defense' => $defense,
        ];
    }
}
