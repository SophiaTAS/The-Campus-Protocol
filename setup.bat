@echo off
setlocal enabledelayedexpansion

set "ROOT=%~dp0"
cd /d "%ROOT%"

echo === The Campus Protocol :: Setup ===

echo.
echo [1/5] Checking PHP...
set "PHP_BIN="
where php >nul 2>&1
if %errorlevel%==0 (
  for /f "delims=" %%P in ('where php') do (
    set "PHP_BIN=%%P"
    goto :php_found
  )
)

if exist "%ROOT%php\php.exe" (
  set "PHP_BIN=%ROOT%php\php.exe"
  goto :php_found
)

:php_missing
echo PHP not found. Downloading a local PHP runtime...
set "PHP_URL=https://windows.php.net/downloads/releases/php-8.1.13-Win32-vs16-x64.zip"
set "PHP_ZIP=%ROOT%php.zip"
set "PHP_DIR=%ROOT%php"

powershell -NoProfile -Command "Invoke-WebRequest -Uri '%PHP_URL%' -OutFile '%PHP_ZIP%'"
if not exist "%PHP_ZIP%" (
  echo [ERROR] PHP download failed. Please install PHP or place it in .\php\
  exit /b 1
)

powershell -NoProfile -Command "Expand-Archive -LiteralPath '%PHP_ZIP%' -DestinationPath '%PHP_DIR%'"
del /f /q "%PHP_ZIP%" >nul 2>&1

if not exist "%PHP_DIR%\php.exe" (
  echo [ERROR] PHP extraction failed.
  exit /b 1
)

if not exist "%PHP_DIR%\php.ini" (
  if exist "%PHP_DIR%\php.ini-development" copy /y "%PHP_DIR%\php.ini-development" "%PHP_DIR%\php.ini" >nul
)

set "PHP_BIN=%PHP_DIR%\php.exe"

:php_found
echo PHP OK: %PHP_BIN%

echo.
echo [2/5] Checking Composer...
set "COMPOSER_BIN="
set "COMPOSER_PHAR="
where composer >nul 2>&1
if %errorlevel%==0 (
  set "COMPOSER_BIN=composer"
) else (
  if not exist "%ROOT%composer.phar" (
    echo Composer not found. Downloading composer.phar...
    powershell -NoProfile -Command "Invoke-WebRequest -Uri 'https://getcomposer.org/download/latest-stable/composer.phar' -OutFile '%ROOT%composer.phar'"
  )
  if exist "%ROOT%composer.phar" (
    set "COMPOSER_PHAR=%ROOT%composer.phar"
  )
)

if "%COMPOSER_BIN%"=="" if "%COMPOSER_PHAR%"=="" (
  echo [ERROR] Composer is not available.
  exit /b 1
)

echo Composer OK.

echo.
echo [3/5] Installation des dependances PHP...
if not "%COMPOSER_BIN%"=="" (
  call %COMPOSER_BIN% install --no-interaction --prefer-dist
) else (
  call %PHP_BIN% "%COMPOSER_PHAR%" install --no-interaction --prefer-dist
)
if %errorlevel% neq 0 (
  echo [ERROR] Composer install failed.
  exit /b 1
)

echo.
echo [4/5] Verification de la base...
set "DB_PATH=%ROOT%var\data.db"
if not exist "%ROOT%var" mkdir "%ROOT%var"

if exist "%DB_PATH%" (
  echo Database found: %DB_PATH%
) else (
  echo Database not found: %DB_PATH%
  set /p RUN_DB="Lancer migrations + seed maintenant ? (o/N): "
  if /i "!RUN_DB!"=="o" (
    del /f /q "%DB_PATH%" >nul 2>&1
    copy /y NUL "%DB_PATH%" >nul
    call %PHP_BIN% bin\console doctrine:migrations:migrate --env=prod --no-interaction
    if %errorlevel% neq 0 exit /b 1
    call %PHP_BIN% scripts\seed_sqlite.php
    if %errorlevel% neq 0 exit /b 1
  ) else (
    echo Skipped database setup.
  )
)

echo.
echo [5/5] Termine.
set /p RUN_SERVERS="Lancer les serveurs maintenant ? (o/N): "
if /i "!RUN_SERVERS!"=="o" (
  call "%ROOT%run-prod.bat"
) else (
  echo Vous pouvez lancer plus tard avec: .\run-prod.bat
)

echo.
echo Setup finished.
endlocal
