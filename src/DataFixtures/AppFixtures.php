<?php

namespace App\DataFixtures;

use App\Entity\Attaque;
use App\Entity\Creature;
use App\Entity\StatutEffet;
use App\Entity\Type;
use App\Entity\TypeMultiplicateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $typeById = [];
        $types = [
            1 => ['nom' => 'Électrique', 'icone' => '⚡', 'couleur' => '#FFEB3B'],
            2 => ['nom' => 'Neutre', 'icone' => '⚪', 'couleur' => '#9E9E9E'],
            3 => ['nom' => 'Néon', 'icone' => '🌈', 'couleur' => '#FF00FF'],
            4 => ['nom' => 'Erreur', 'icone' => '👾', 'couleur' => '#F44336'],
            5 => ['nom' => 'Virus', 'icone' => '☣️', 'couleur' => '#9C27B0'],
            6 => ['nom' => 'Acier', 'icone' => '⚙️', 'couleur' => '#607D8B'],
            7 => ['nom' => 'Organique', 'icone' => '🌿', 'couleur' => '#4CAF50'],
            8 => ['nom' => 'Feu', 'icone' => '🔥', 'couleur' => '#FF5722'],
            9 => ['nom' => 'Spectre', 'icone' => '👻', 'couleur' => '#3F51B5'],
        ];

        foreach ($types as $id => $data) {
            $type = (new Type())
                ->setNom($data['nom'])
                ->setIcone($data['icone'])
                ->setCouleur($data['couleur']);
            $manager->persist($type);
            $typeById[$id] = $type;
        }

        $statutById = [];
        $statuts = [
            1 => ['nom' => 'TURBO_ON', 'description' => 'Overclock du CPU : Attaque +', 'icone' => '⏩', 'duree' => 3],
            2 => ['nom' => 'RESTORE_POINT', 'description' => 'Récupération de données : PV +', 'icone' => '💾', 'duree' => 3],
            3 => ['nom' => 'FRAME_DROP', 'description' => 'Baisse de FPS : Lag', 'icone' => '📉', 'duree' => 2],
            4 => ['nom' => 'BIT_ROT', 'description' => 'Les bits pourrissent : Dégâts réguliers', 'icone' => '☣️', 'duree' => 4],
            5 => ['nom' => 'LOW_RES', 'description' => 'Résolution réduite : Défense -', 'icone' => '📺', 'duree' => 2],
            6 => ['nom' => 'SYSTEM_HALT', 'description' => 'Arrêt total : Impossible d\'agir', 'icone' => '🛑', 'duree' => 1],
        ];

        foreach ($statuts as $id => $data) {
            $statut = (new StatutEffet())
                ->setNom($data['nom'])
                ->setDescription($data['description'])
                ->setIcone($data['icone'])
                ->setDuree($data['duree']);
            $manager->persist($statut);
            $statutById[$id] = $statut;
        }

        $creatureById = [];
        $creatures = [
            1 => ['nom' => 'Câblo', 'pv_max' => 100, 'attaque' => 65, 'defens' => 55, 'image' => '/img/creature/cablo.png', 'type_id' => 1],
            2 => ['nom' => 'Poussiéreux', 'pv_max' => 120, 'attaque' => 50, 'defens' => 70, 'image' => '/img/creature/poussierieux.png', 'type_id' => 2],
            3 => ['nom' => 'Vapor-Wave', 'pv_max' => 90, 'attaque' => 80, 'defens' => 45, 'image' => '/img/creature/vaporwave.png', 'type_id' => 3],
            4 => ['nom' => 'Buggy', 'pv_max' => 85, 'attaque' => 75, 'defens' => 50, 'image' => '/img/creature/buggy.png', 'type_id' => 4],
            5 => ['nom' => 'Spammeur', 'pv_max' => 95, 'attaque' => 70, 'defens' => 60, 'image' => '/img/creature/spammeur.png', 'type_id' => 5],
            6 => ['nom' => 'Craker', 'pv_max' => 110, 'attaque' => 85, 'defens' => 80, 'image' => '/img/creature/craker.png', 'type_id' => 6],
            7 => ['nom' => 'Macrob', 'pv_max' => 115, 'attaque' => 60, 'defens' => 65, 'image' => '/img/creature/macrob.png', 'type_id' => 7],
            8 => ['nom' => 'Bit-Rex', 'pv_max' => 130, 'attaque' => 90, 'defens' => 70, 'image' => '/img/creature/bitrex.png', 'type_id' => 8],
            9 => ['nom' => 'Kernel-Panic', 'pv_max' => 105, 'attaque' => 95, 'defens' => 40, 'image' => '/img/creature/kernelpanic.png', 'type_id' => 9],
        ];

        foreach ($creatures as $id => $data) {
            $creature = (new Creature())
                ->setNom($data['nom'])
                ->setPvMax($data['pv_max'])
                ->setAttaque($data['attaque'])
                ->setDefens($data['defens'])
                ->setImage($data['image'])
                ->setType($typeById[$data['type_id']]);
            $manager->persist($creature);
            $creatureById[$id] = $creature;
        }

        $attaques = [
            ['nom' => 'Blast Processing', 'degats' => 30, 'chance' => 1.0, 'creature_id' => 1, 'statut_effet_id' => null],
            ['nom' => 'Étincelle GameGear', 'degats' => 20, 'chance' => 0.4, 'creature_id' => 1, 'statut_effet_id' => 3],
            ['nom' => 'Master System Link', 'degats' => 0, 'chance' => 1.0, 'creature_id' => 1, 'statut_effet_id' => 1],

            ['nom' => 'Souffle de Cartouche', 'degats' => 20, 'chance' => 1.0, 'creature_id' => 2, 'statut_effet_id' => null],
            ['nom' => 'Flou GameBoy', 'degats' => 5, 'chance' => 0.5, 'creature_id' => 2, 'statut_effet_id' => 5],
            ['nom' => 'Pile de Sauvegarde', 'degats' => 0, 'chance' => 1.0, 'creature_id' => 2, 'statut_effet_id' => 2],

            ['nom' => 'Guru Meditation', 'degats' => 15, 'chance' => 0.4, 'creature_id' => 3, 'statut_effet_id' => 6],
            ['nom' => 'Mode 7 Rotation', 'degats' => 35, 'chance' => 1.0, 'creature_id' => 3, 'statut_effet_id' => null],
            ['nom' => 'Copper List Blast', 'degats' => 40, 'chance' => 0.8, 'creature_id' => 3, 'statut_effet_id' => null],

            ['nom' => 'MissingNo Slash', 'degats' => 25, 'chance' => 1.0, 'creature_id' => 4, 'statut_effet_id' => null],
            ['nom' => 'Kill Screen', 'degats' => 15, 'chance' => 0.5, 'creature_id' => 4, 'statut_effet_id' => 3],
            ['nom' => 'Sprite Flickering', 'degats' => 20, 'chance' => 0.9, 'creature_id' => 4, 'statut_effet_id' => 5],

            ['nom' => 'Tape Loading Noise', 'degats' => 20, 'chance' => 0.6, 'creature_id' => 5, 'statut_effet_id' => 3],
            ['nom' => 'BASIC Overflow', 'degats' => 25, 'chance' => 1.0, 'creature_id' => 5, 'statut_effet_id' => null],
            ['nom' => 'Poke 53280', 'degats' => 0, 'chance' => 1.0, 'creature_id' => 5, 'statut_effet_id' => 1],

            ['nom' => 'Atari 2600 Beam', 'degats' => 30, 'chance' => 1.0, 'creature_id' => 6, 'statut_effet_id' => null],
            ['nom' => 'Vector Graphics', 'degats' => 35, 'chance' => 1.0, 'creature_id' => 6, 'statut_effet_id' => null],
            ['nom' => 'Joystik Snap', 'degats' => 40, 'chance' => 0.4, 'creature_id' => 6, 'statut_effet_id' => 5],

            ['nom' => 'Turbo Button', 'degats' => 0, 'chance' => 1.0, 'creature_id' => 7, 'statut_effet_id' => 1],
            ['nom' => 'Scanline Strike', 'degats' => 25, 'chance' => 1.0, 'creature_id' => 7, 'statut_effet_id' => null],
            ['nom' => 'Choc de Clavier', 'degats' => 30, 'chance' => 0.9, 'creature_id' => 7, 'statut_effet_id' => null],

            ['nom' => 'Neo-Geo Power', 'degats' => 45, 'chance' => 0.8, 'creature_id' => 8, 'statut_effet_id' => null],
            ['nom' => '100 Mega Shock', 'degats' => 35, 'chance' => 1.0, 'creature_id' => 8, 'statut_effet_id' => null],
            ['nom' => 'Coin-Op Burn', 'degats' => 15, 'chance' => 0.4, 'creature_id' => 8, 'statut_effet_id' => 4],

            ['nom' => 'Assembly Crash', 'degats' => 40, 'chance' => 1.0, 'creature_id' => 9, 'statut_effet_id' => null],
            ['nom' => 'Stack Overflow', 'degats' => 20, 'chance' => 0.4, 'creature_id' => 9, 'statut_effet_id' => 4],
            ['nom' => 'Illegal Instruction', 'degats' => 50, 'chance' => 0.6, 'creature_id' => 9, 'statut_effet_id' => null],
        ];

        foreach ($attaques as $data) {
            $attaque = (new Attaque())
                ->setNom($data['nom'])
                ->setDegats($data['degats'])
                ->setChance($data['chance'])
                ->setCreature($creatureById[$data['creature_id']]);

            if ($data['statut_effet_id'] !== null) {
                $attaque->setStatutEffet($statutById[$data['statut_effet_id']]);
            }

            $manager->persist($attaque);
        }

        $multiplicateurs = [
            ['type_source_id' => 5, 'type_cible_id' => 3, 'multiplicateur' => 1.5],
            ['type_source_id' => 4, 'type_cible_id' => 6, 'multiplicateur' => 1.5],
            ['type_source_id' => 1, 'type_cible_id' => 3, 'multiplicateur' => 1.5],
            ['type_source_id' => 9, 'type_cible_id' => 1, 'multiplicateur' => 1.5],
            ['type_source_id' => 8, 'type_cible_id' => 7, 'multiplicateur' => 1.5],
            ['type_source_id' => 8, 'type_cible_id' => 2, 'multiplicateur' => 1.5],
            ['type_source_id' => 3, 'type_cible_id' => 5, 'multiplicateur' => 0.5],
            ['type_source_id' => 6, 'type_cible_id' => 4, 'multiplicateur' => 0.5],
            ['type_source_id' => 7, 'type_cible_id' => 8, 'multiplicateur' => 0.5],
        ];

        foreach ($multiplicateurs as $data) {
            $multiplicateur = (new TypeMultiplicateur())
                ->setTypeSource($typeById[$data['type_source_id']])
                ->setTypeCible($typeById[$data['type_cible_id']])
                ->setMultiplicateur($data['multiplicateur']);
            $manager->persist($multiplicateur);
        }

        $manager->flush();
    }
}
