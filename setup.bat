@echo off
setlocal enabledelayedexpansion

set "ROOT=%~dp0"
cd /d "%ROOT%"

echo === The Campus Protocol :: Setup ===

echo.
echo [1/6] Checking PHP...
set "PHP_BIN="
set "PHP_DIR=%ROOT%php"
if exist "%PHP_DIR%\php.exe" (
  set "PHP_BIN=%PHP_DIR%\php.exe"
  goto :php_found
)
where php >nul 2>&1
if %errorlevel%==0 (
  for /f "delims=" %%P in ('where php') do (
    set "PHP_BIN=%%P"
    goto :php_found
  )
)

:php_missing
echo PHP not found. Downloading a local PHP runtime...
set "PHP_URL=https://windows.php.net/downloads/releases/latest/php-8.5-Win32-vs16-x64-latest.zip"
set "PHP_ZIP=%ROOT%php.zip"
set "PHP_DIR=%ROOT%php"

where curl >nul 2>&1
if %errorlevel%==0 (
  curl -L -o "%PHP_ZIP%" "%PHP_URL%"
) else (
  powershell -NoProfile -Command "$ProgressPreference='SilentlyContinue'; [Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri '%PHP_URL%' -OutFile '%PHP_ZIP%' -UseBasicParsing -MaximumRedirection 10;"
)
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
  set "PHP_TEMPLATE="
  if exist "C:\wamp64\bin\php\php-8.5.2\php.ini" set "PHP_TEMPLATE=C:\wamp64\bin\php\php-8.5.2\php.ini"
  if defined PHP_TEMPLATE (
    copy /y "%PHP_TEMPLATE%" "%PHP_DIR%\php.ini" >nul
  ) else (
    if exist "%PHP_DIR%\php.ini-development" copy /y "%PHP_DIR%\php.ini-development" "%PHP_DIR%\php.ini" >nul
  )
)

set "PHP_BIN=%PHP_DIR%\php.exe"

:php_found
echo PHP OK: %PHP_BIN%
for /f "tokens=1,2" %%A in ('cmd /s /c ""%PHP_BIN%" -r "echo PHP_MAJOR_VERSION . ' ' . PHP_MINOR_VERSION;""') do (
  set "PHP_MAJOR=%%A"
  set "PHP_MINOR=%%B"
)
if %PHP_MAJOR% LSS 8 (
  echo [ERROR] PHP 8.4+ is required for Symfony 8. Detected %PHP_MAJOR%.%PHP_MINOR%.
  exit /b 1
)
if %PHP_MAJOR%==8 if %PHP_MINOR% LSS 4 (
  echo [ERROR] PHP 8.4+ is required for Symfony 8. Detected %PHP_MAJOR%.%PHP_MINOR%.
  exit /b 1
)

echo.
echo [2/6] Checking PHP extensions...
set "PHP_INI=%PHP_DIR%\php.ini"
if not exist "%PHP_INI%" (
  set "PHP_INI="
  for /f "tokens=2,* delims=:" %%A in ('"%PHP_BIN%" --ini ^| findstr /i "Loaded Configuration File"') do (
    set "PHP_INI=%%B"
  )
  set "PHP_INI=%PHP_INI:~1%"
)
if "%PHP_INI%"=="" (
  echo [WARN] php.ini not found via --ini output.
) else (
  echo php.ini: %PHP_INI%
)
for /f %%M in ('"%PHP_BIN%" -m ^| findstr /i "pdo_sqlite"') do set "HAS_PDO_SQLITE=1"
for /f %%M in ('"%PHP_BIN%" -m ^| findstr /i "sqlite3"') do set "HAS_SQLITE3=1"
if not defined HAS_PDO_SQLITE (
  if /i "%PHP_INI%"=="%ROOT%php\\php.ini" (
    powershell -NoProfile -Command "(Get-Content '%ROOT%php\\php.ini') -replace '^;extension=pdo_sqlite','extension=pdo_sqlite' | Set-Content -Encoding ASCII '%ROOT%php\\php.ini'"
    powershell -NoProfile -Command "(Get-Content '%ROOT%php\\php.ini') -replace '^;extension=sqlite3','extension=sqlite3' | Set-Content -Encoding ASCII '%ROOT%php\\php.ini'"
    echo Enabled pdo_sqlite and sqlite3 in local php.ini.
  ) else (
    echo [WARN] pdo_sqlite extension is missing. Please enable it in %PHP_INI%.
  )
)

echo.
echo [3/6] Checking Composer...
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
echo [4/6] Installation des dependances PHP...
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
echo [5/6] Verification de la base...
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
echo [6/6] Termine.
set /p RUN_SERVERS="Lancer les serveurs maintenant ? (o/N): "
if /i "!RUN_SERVERS!"=="o" (
  call "%ROOT%run-prod.bat"
) else (
  echo Vous pouvez lancer plus tard avec: .\run-prod.bat
)

echo.
echo Setup finished.
endlocal
