<?php

declare(strict_types=1);

$dbPath = __DIR__ . '/../var/data.db';
if (!file_exists($dbPath)) {
    fwrite(STDERR, "[ERREUR] Base SQLite introuvable: {$dbPath}\n");
    exit(1);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = OFF');

    $pdo->beginTransaction();

    // Nettoyage
    $pdo->exec('DELETE FROM attaque');
    $pdo->exec('DELETE FROM creature');
    $pdo->exec('DELETE FROM type_multiplicateur');
    $pdo->exec('DELETE FROM statut_effet');
    $pdo->exec('DELETE FROM type');
    $pdo->exec('DELETE FROM arenes');
    $pdo->exec("DELETE FROM sqlite_sequence WHERE name IN ('attaque','creature','type_multiplicateur','statut_effet','type','arenes')");

    // Types
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
    $stmt = $pdo->prepare('INSERT INTO type (id, nom, icone, couleur) VALUES (:id, :nom, :icone, :couleur)');
    foreach ($types as $id => $data) {
        $stmt->execute([
            ':id' => $id,
            ':nom' => $data['nom'],
            ':icone' => $data['icone'],
            ':couleur' => $data['couleur'],
        ]);
    }

    // Statuts
    $statuts = [
        1 => [
            'nom' => 'TURBO_ON',
            'description' => 'Overclock du CPU : Attaque +',
            'icone' => '⏩',
            'duree' => 3,
            'type_effet' => 'boost',
            'cible' => 'lanceur',
            'cible_stat' => 'attaque',
        ],
        2 => [
            'nom' => 'RESTORE_POINT',
            'description' => 'Récupération de données : PV +',
            'icone' => '💾',
            'duree' => 3,
            'type_effet' => 'regen',
            'cible' => 'lanceur',
            'cible_stat' => null,
        ],
        3 => [
            'nom' => 'FRAME_DROP',
            'description' => 'Baisse de FPS : Lag',
            'icone' => '📉',
            'duree' => 2,
            'type_effet' => 'malus',
            'cible' => 'cible',
            'cible_stat' => 'attaque',
        ],
        4 => [
            'nom' => 'BIT_ROT',
            'description' => 'Les bits pourrissent : Dégâts réguliers',
            'icone' => '☣️',
            'duree' => 4,
            'type_effet' => 'degats_tour',
            'cible' => 'cible',
            'cible_stat' => null,
        ],
        5 => [
            'nom' => 'LOW_RES',
            'description' => 'Résolution réduite : Défense -',
            'icone' => '📺',
            'duree' => 2,
            'type_effet' => 'malus',
            'cible' => 'cible',
            'cible_stat' => 'defense',
        ],
        6 => [
            'nom' => 'SYSTEM_HALT',
            'description' => "Arrêt total : Impossible d'agir",
            'icone' => '🛑',
            'duree' => 1,
            'type_effet' => 'paralyse_logicielle',
            'cible' => 'cible',
            'cible_stat' => null,
        ],
    ];
    $stmt = $pdo->prepare('INSERT INTO statut_effet (id, nom, description, icone, duree, type_effet, cible, cible_stat) VALUES (:id, :nom, :description, :icone, :duree, :type_effet, :cible, :cible_stat)');
    foreach ($statuts as $id => $data) {
        $stmt->execute([
            ':id' => $id,
            ':nom' => $data['nom'],
            ':description' => $data['description'],
            ':icone' => $data['icone'],
            ':duree' => $data['duree'],
            ':type_effet' => $data['type_effet'],
            ':cible' => $data['cible'],
            ':cible_stat' => $data['cible_stat'],
        ]);
    }

    // Creatures
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
    $stmt = $pdo->prepare('INSERT INTO creature (id, nom, pv_max, attaque, defens, image, type_id) VALUES (:id, :nom, :pv_max, :attaque, :defens, :image, :type_id)');
    foreach ($creatures as $id => $data) {
        $stmt->execute([
            ':id' => $id,
            ':nom' => $data['nom'],
            ':pv_max' => $data['pv_max'],
            ':attaque' => $data['attaque'],
            ':defens' => $data['defens'],
            ':image' => $data['image'],
            ':type_id' => $data['type_id'],
        ]);
    }

    // Attaques
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
    $stmt = $pdo->prepare('INSERT INTO attaque (nom, degats, chance, creature_id, statut_effet_id) VALUES (:nom, :degats, :chance, :creature_id, :statut_effet_id)');
    foreach ($attaques as $data) {
        $stmt->execute([
            ':nom' => $data['nom'],
            ':degats' => $data['degats'],
            ':chance' => $data['chance'],
            ':creature_id' => $data['creature_id'],
            ':statut_effet_id' => $data['statut_effet_id'],
        ]);
    }

    // Multiplicateurs
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
    $stmt = $pdo->prepare('INSERT INTO type_multiplicateur (type_source_id, type_cible_id, multiplicateur) VALUES (:type_source_id, :type_cible_id, :multiplicateur)');
    foreach ($multiplicateurs as $data) {
        $stmt->execute([
            ':type_source_id' => $data['type_source_id'],
            ':type_cible_id' => $data['type_cible_id'],
            ':multiplicateur' => $data['multiplicateur'],
        ]);
    }

    // Arènes
    $arenes = [
        ['nom' => "Le Hall de l'Anti-Aliasing", 'inspiration' => 'Nintendo 64', 'style' => 'Réaliste', 'image_path' => 'img/arene/GoldenTurok.png'],
        ['nom' => 'Zone Blast Processing', 'inspiration' => 'Mega Drive', 'style' => 'Réaliste', 'image_path' => 'img/arene/Green_Hill_Night.png'],
        ['nom' => 'Le Hub VMU', 'inspiration' => 'Dreamcast', 'style' => 'Réaliste', 'image_path' => 'img/arene/HUB_VMU.png'],
        ['nom' => 'La Baie des Transparences', 'inspiration' => 'Saturn', 'style' => 'Réaliste', 'image_path' => 'img/arene/Nights_into_Matrix.png'],
        ['nom' => 'La Forêt Woodgrain', 'inspiration' => 'Atari 2600', 'style' => 'Réaliste', 'image_path' => 'img/arene/Pitfall_Nightmare.png'],
        ['nom' => 'Le Donjon Guru Meditation', 'inspiration' => 'Amiga 500', 'style' => 'Réaliste', 'image_path' => 'img/arene/Shadow_of_the_Beast.png'],
        ['nom' => 'La Place de la Spirale', 'inspiration' => 'Dreamcast', 'style' => 'Réaliste', 'image_path' => 'img/arene/Sonic_adventure_dream.png'],
        ['nom' => 'Le Labo System 16', 'inspiration' => 'Arcade Sega', 'style' => 'Réaliste', 'image_path' => 'img/arene/Systeme16_Arena.png'],
        ['nom' => 'Le Palais Master System', 'inspiration' => 'Master System', 'style' => 'Réaliste', 'image_path' => 'img/arene/Wonder_Master_Kidd.png'],
        ['nom' => 'La Dimension Glitched Famicom', 'inspiration' => 'GameBoy', 'style' => 'Réaliste', 'image_path' => 'img/arene/Zelda_beach.png'],
    ];
    $stmt = $pdo->prepare('INSERT INTO arenes (nom, inspiration, style, image_path) VALUES (:nom, :inspiration, :style, :image_path)');
    foreach ($arenes as $data) {
        $stmt->execute([
            ':nom' => $data['nom'],
            ':inspiration' => $data['inspiration'],
            ':style' => $data['style'],
            ':image_path' => $data['image_path'],
        ]);
    }

    $pdo->commit();
    $pdo->exec('PRAGMA foreign_keys = ON');

    echo "[OK] Seed SQLite termine.\n";
    exit(0);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "[ERREUR] Seed SQLite: " . $e->getMessage() . "\n");
    exit(1);
}
