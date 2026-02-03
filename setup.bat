@echo off
setlocal EnableDelayedExpansion

set "ROOT=%~dp0"
cd /d "%ROOT%"
if defined AUTO_YES set "AUTO_YES=1"

set "PHP_DIR=%ROOT%php"
set "PHP_BIN=%PHP_DIR%\php.exe"
set "PHP_INI=%PHP_DIR%\php.ini"
set "PHP_URL=https://windows.php.net/downloads/releases/latest/php-8.5-Win32-vs17-x64-latest.zip"
set "PHP_ZIP=%ROOT%php.zip"
set "COMPOSER_PHAR=%ROOT%composer.phar"

call :print_header

echo.
call :confirm "[1/6] Telecharger PHP 8.5.x dans .\php\ (ecrase si existe)" || exit /b 1
call :download_php || exit /b 1

echo.
call :confirm "[2/6] Configurer php.ini pour l'application" || exit /b 1
call :configure_php_ini || exit /b 1

echo.
call :confirm "[3/6] Telecharger Composer (composer.phar)" || exit /b 1
call :download_composer || exit /b 1

echo.
call :confirm "[4/6] Installer les dependances PHP (composer install)" || exit /b 1
call :composer_install || exit /b 1

echo.
call :confirm "[5/6] Creer la base SQLite + migrations + seed" || exit /b 1
call :setup_db || exit /b 1

echo.
call :confirm "[6/6] Lancer run-prod.bat" || exit /b 0
call "%ROOT%run-prod.bat"

echo.
echo Setup finished.
endlocal
exit /b 0

:print_header
echo === The Campus Protocol :: Setup ===
exit /b 0

:confirm
set "PROMPT=%~1"
if defined AUTO_YES exit /b 0
set /p CONFIRM="%PROMPT% (o/N): "
if /i "%CONFIRM%"=="o" exit /b 0
exit /b 1

:download_php
if exist "%PHP_DIR%" rmdir /s /q "%PHP_DIR%"
if exist "%PHP_ZIP%" del /f /q "%PHP_ZIP%" >nul 2>&1

where curl >nul 2>&1
if %errorlevel%==0 (
  curl -L -o "%PHP_ZIP%" "%PHP_URL%"
) else (
  powershell -NoProfile -Command "$ProgressPreference='SilentlyContinue'; [Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri '%PHP_URL%' -OutFile '%PHP_ZIP%' -UseBasicParsing -MaximumRedirection 10;"
)

if not exist "%PHP_ZIP%" (
  echo [ERROR] PHP download failed.
  exit /b 1
)

rem size check removed; extraction + php.exe existence is the validation

powershell -NoProfile -Command "Expand-Archive -LiteralPath '%PHP_ZIP%' -DestinationPath '%PHP_DIR%'"
if errorlevel 1 (
  echo [ERROR] PHP extraction failed.
  exit /b 1
)

if not exist "%PHP_BIN%" (
  echo [ERROR] php.exe introuvable apres extraction.
  exit /b 1
)

echo PHP OK: %PHP_BIN%
exit /b 0

:configure_php_ini
if not exist "%PHP_INI%" (
  if exist "%PHP_DIR%\php.ini-development" (
    copy /y "%PHP_DIR%\php.ini-development" "%PHP_INI%" >nul
  ) else if exist "%PHP_DIR%\php.ini-production" (
    copy /y "%PHP_DIR%\php.ini-production" "%PHP_INI%" >nul
  ) else (
    echo [ERROR] Aucun template php.ini trouve.
    exit /b 1
  )
)

>> "%PHP_INI%" echo.
>> "%PHP_INI%" echo ; Added by setup.bat
>> "%PHP_INI%" echo extension_dir = "%PHP_DIR%\ext"
>> "%PHP_INI%" echo extension=openssl
>> "%PHP_INI%" echo extension=pdo_sqlite
>> "%PHP_INI%" echo extension=sqlite3
>> "%PHP_INI%" echo extension=sodium

echo php.ini OK: %PHP_INI%
exit /b 0

:download_composer
if exist "%COMPOSER_PHAR%" del /f /q "%COMPOSER_PHAR%" >nul 2>&1
powershell -NoProfile -Command "Invoke-WebRequest -Uri 'https://getcomposer.org/download/latest-stable/composer.phar' -OutFile '%COMPOSER_PHAR%'"
if not exist "%COMPOSER_PHAR%" (
  echo [ERROR] Composer download failed.
  exit /b 1
)

echo Composer OK: %COMPOSER_PHAR%
exit /b 0

:composer_install
"%PHP_BIN%" "%COMPOSER_PHAR%" install --no-interaction --prefer-dist
if %errorlevel% neq 0 (
  echo [ERROR] Composer install failed.
  exit /b 1
)
exit /b 0

:setup_db
set "DB_PATH=%ROOT%var\data.db"
if not exist "%ROOT%var" mkdir "%ROOT%var"

del /f /q "%DB_PATH%" >nul 2>&1
copy /y NUL "%DB_PATH%" >nul
"%PHP_BIN%" bin\console doctrine:migrations:migrate --env=prod --no-interaction
if %errorlevel% neq 0 exit /b 1
"%PHP_BIN%" scripts\seed_sqlite.php
if %errorlevel% neq 0 exit /b 1
exit /b 0
