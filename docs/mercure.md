# Mercure obligatoire (sans Docker)

Le projet **nécessite Mercure** pour fonctionner.  
Installe Caddy avec le module Mercure.

## 1) Installer Caddy + Mercure (Windows)

1. Installer Go (https://go.dev/dl/)
2. Installer xcaddy :
```
go install github.com/caddyserver/xcaddy/cmd/xcaddy@latest
```
3. Builder Caddy avec Mercure :
```
xcaddy build --with github.com/dunglas/mercure/caddy
```
4. Place le binaire `caddy.exe` dans un dossier de ton PATH, ou dans ce repo.

## 2) Lancer Mercure

Depuis la racine du projet :
```
caddy run --config Caddyfile.mercure
```

## 3) Variables d'env

Valeurs attendues dans `.env` :
- `MERCURE_URL=http://localhost/.well-known/mercure`
- `MERCURE_PUBLIC_URL=http://localhost/.well-known/mercure`
- `MERCURE_JWT_SECRET=ChangeThisMercureHubJWTSecretKey`
