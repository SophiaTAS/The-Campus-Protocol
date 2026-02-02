@echo off
setlocal DisableDelayedExpansion
if defined TRACE echo on

set "ROOT=%~dp0"
cd /d "%ROOT%"

set "APP_ENV=prod"
set "APP_DEBUG=0"
set "ROOT_DIR=%ROOT%"

call :print_header
call :stop_processes
set "MERCURE_JWT_SECRET="
for /f "usebackq tokens=1* delims==" %%A in (`findstr /R /C:"^MERCURE_JWT_SECRET=" "%ROOT%.env" "%ROOT%.env.local" 2^>nul`) do (
  set "MERCURE_JWT_SECRET=%%B"
)
if defined MERCURE_JWT_SECRET (
  set "MERCURE_JWT_SECRET=%MERCURE_JWT_SECRET:"=%"
) else (
  set "MERCURE_JWT_SECRET=dev-secret"
)
call :find_php
call :ensure_php_ini
call :reset_db
call :start_php_cgi
call :start_mercure
echo Ouverture du navigateur...
timeout /t 2 /nobreak >nul
start "" "http://127.0.0.1:3000/"

echo.
echo Serveurs demarres.
pause
endlocal
exit /b 0

:print_header
echo === The Campus Protocol :: Run (prod) ===
exit /b 0

:stop_processes
echo Arret des processus existants (si besoin)
taskkill /IM php-cgi.exe /F >nul 2>&1
taskkill /IM mercure.exe /F >nul 2>&1
taskkill /IM caddy.exe /F >nul 2>&1
exit /b 0

:find_php
set "PHP_DIR=%ROOT%php"
set "PHP_CGI="
set "PHP_EXE="
if exist "%PHP_DIR%\php.exe" set "PHP_EXE=%PHP_DIR%\php.exe"
if exist "%PHP_DIR%\php-cgi.exe" set "PHP_CGI=%PHP_DIR%\php-cgi.exe"
if not defined PHP_CGI (
  for /f "delims=" %%P in ('where php-cgi 2^>nul') do (
    set "PHP_CGI=%%P"
    goto :php_found
  )
)
:php_found
if not defined PHP_CGI (
  echo [ERREUR] php-cgi.exe introuvable.
  pause
  exit /b 1
)
if not defined PHP_EXE (
  for %%X in ("%PHP_CGI%") do set "PHP_EXE=%%~dpXphp.exe"
)
if not exist "%PHP_EXE%" (
  echo [ERREUR] php.exe introuvable.
  pause
  exit /b 1
)
exit /b 0

:ensure_php_ini
if not exist "%PHP_DIR%\php.ini" (
  if exist "%PHP_DIR%\php.ini-development" copy /y "%PHP_DIR%\php.ini-development" "%PHP_DIR%\php.ini" >nul
  if exist "%PHP_DIR%\php.ini-production" copy /y "%PHP_DIR%\php.ini-production" "%PHP_DIR%\php.ini" >nul
)
exit /b 0

:reset_db
echo Reinitialisation de la base SQLite
mkdir "%ROOT%var" >nul 2>&1
del /f /q "%ROOT%var\data.db" >nul 2>&1
if exist "%ROOT%var\data.db" goto :db_locked
copy /y NUL "%ROOT%var\data.db" >nul
if errorlevel 1 goto :fail_db
"%PHP_EXE%" bin\console doctrine:migrations:migrate --env=prod --no-interaction
if errorlevel 1 goto :fail_migrate
"%PHP_EXE%" scripts\seed_sqlite.php
if errorlevel 1 goto :fail_seed
exit /b 0

:db_locked
echo [ERREUR] Impossible de supprimer var\data.db (verrouille).
pause
exit /b 1

:start_php_cgi
echo Demarrage de PHP-CGI...
start "PHP-CGI (NE PAS FERMER)" cmd /k ""%PHP_CGI%" -b 127.0.0.1:9000"
exit /b 0

:start_mercure
echo Demarrage de Caddy/Mercure...
if not exist "%ROOT%mercure.exe" (
  echo [ERREUR] mercure.exe introuvable.
  pause
  exit /b 1
)
if not exist "%ROOT%Caddyfile.prod" (
  echo [ERREUR] Caddyfile.prod introuvable.
  pause
  exit /b 1
)
start "Caddy/Mercure (NE PAS FERMER)" cmd /k "set ROOT_DIR=%ROOT_DIR%&&set MERCURE_JWT_SECRET=%MERCURE_JWT_SECRET%&&""%ROOT%mercure.exe"" run --config ""%ROOT%Caddyfile.prod"""
exit /b 0

:fail_db
echo [ERREUR] Creation de la base impossible.
pause
exit /b 1

:fail_migrate
echo [ERREUR] Migrations echouees.
pause
exit /b 1

:fail_seed
echo [ERREUR] Seed SQLite echoue.
pause
exit /b 1
