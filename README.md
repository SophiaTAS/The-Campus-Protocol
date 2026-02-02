# The Campus Protocol

## Avertissement
Le projet est encore en developpement.  
Il est prevu pour tourner en local uniquement, avec une securite volontairement faible.

## Pitch / Lore
The Campus Protocol est un petit jeu web de combats tour par tour au parfum retro (années 80/90/2000).  
Les créatures et attaques sont inspirées de la culture hardware/IT (cartouches, pixels, bugs, overclock, etc.).  
L’objectif : une expérience rapide, lisible, et fun — sans lourde infrastructure.

## Etat actuel (ce que le projet fait)
- Combats 1v1 joueur vs IA (tour par tour).
- Bestiaire (liste des creatures et stats).
- Arènes thématiques.
- Ecran de resultat de combat.
- Style retro + ambiance sonore.
- Temps reel leger via Mercure (retour d’actions de combat).

## Ce que le projet ne fait pas (encore)
- PvP complet persistant.
- Mode histoire scénarisé.
- Gestion d’objets / inventaire.
- Administration de contenu.
- Economie / boutique.
- Multi-langues.

## Evolutions prevues (backlog)
- IA plus avancee (strategie, difficulte).
- Mode histoire + progression.
- PvP complet (lobby + matchmaking).
- Systeme d’objets et drops.
- Plus d’arenes, creatures et effets.

## Ce projet est volontairement minimal
Le coeur est fonctionnel, mais le reste est volontairement réduit faute de temps.  
Tout est construit pour rester simple a installer, facile a comprendre, et evolutif.


## Prerequis (Windows)
- Windows 10/11
- PHP 8.1 (php.exe + php-cgi.exe)
- Composer

Optionnel (recommande) :
- Symfony CLI si vous voulez un mode dev.

## Lancement rapide (avec scripts)
1. Cloner le depot.
2. Ouvrir un terminal dans le dossier du projet.
3. Installer les dependances et verifier l'environnement :
   - `.\setup.bat`
4. Lancer l'application (prod local) :
   - `.\run-prod.bat`

`run-prod.bat` recrée la base SQLite, lance les migrations, injecte les donnees, puis demarre PHP-CGI et Mercure/Caddy.

## Lancement manuel (sans .bat)
### 1) Installer les dependances
```cmd
composer install --no-interaction --prefer-dist
```

### 2) Creer la base SQLite + migrations + seed
```cmd
mkdir var
del /f /q var\data.db
copy /y NUL var\data.db
php bin\console doctrine:migrations:migrate --env=prod --no-interaction
php scripts\seed_sqlite.php
```

### 3) Demarrer le serveur PHP-CGI (prod local)
```cmd
php-cgi.exe -b 127.0.0.1:9000
```

### 4) Demarrer Mercure/Caddy
```cmd
set MERCURE_JWT_SECRET=ChangeThisMercureHubJWTSecretKey
set ROOT_DIR=%cd%\
mercure.exe run --config Caddyfile.prod
```

### 5) Ouvrir l’application
Dans le navigateur : `http://127.0.0.1:3000/`

## Notes utiles
- La base SQLite utilise `var/data.db`.
- Le seed est fait via `scripts/seed_sqlite.php` (base des donnees issue de AppFixtures).
- Si vous modifiez `MERCURE_JWT_SECRET` dans `.env`, utilisez la meme valeur au lancement de Mercure.
