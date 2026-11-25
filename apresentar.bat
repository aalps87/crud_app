@echo off
setlocal EnableExtensions
chcp 65001 >nul
title AERIS - preparando o projeto

rem ==========================================================================
rem  AERIS - baixa e roda o projeto em qualquer Windows 10/11, sem instalar nada
rem  e sem precisar de administrador. Basta dar dois cliques neste arquivo.
rem  Na primeira vez demora alguns minutos (baixa PHP, Composer e o projeto).
rem ==========================================================================

set "REPO=https://github.com/aalps87/crud_app/archive/refs/heads/main.zip"
set "PHP_ZIP=https://windows.php.net/downloads/releases/latest/php-8.3-nts-Win32-vs16-x64-latest.zip"
set "COMPOSER=https://getcomposer.org/download/latest-stable/composer.phar"
set "PORT=8000"

rem Se o script estiver dentro do projeto, usa essa pasta. Senao, cria uma pasta AERIS ao lado.
if exist "%~dp0artisan" (
    set "APP=%~dp0"
) else (
    set "APP=%~dp0AERIS\"
)
set "TOOLS=%LOCALAPPDATA%\aeris-tools"
set "PHP=%TOOLS%\php\php.exe"

where curl >nul 2>nul || (echo [ERRO] Este Windows nao tem curl. Precisa do Windows 10 ou mais novo. & goto :fim)
where tar  >nul 2>nul || (echo [ERRO] Este Windows nao tem tar. Precisa do Windows 10 ou mais novo. & goto :fim)

echo.
echo  [1/6] Baixando o projeto do GitHub...
if not exist "%APP%artisan" (
    curl -L --fail -o "%TEMP%\aeris.zip" "%REPO%" || (echo [ERRO] Nao consegui baixar o projeto. Confira a internet. & goto :fim)
    if not exist "%TEMP%\aeris-src" mkdir "%TEMP%\aeris-src"
    tar -xf "%TEMP%\aeris.zip" -C "%TEMP%\aeris-src" || goto :fim
    xcopy "%TEMP%\aeris-src\crud_app-main" "%APP%" /E /I /Q /Y >nul
    rmdir /S /Q "%TEMP%\aeris-src"
    del "%TEMP%\aeris.zip"
) else (
    echo        Projeto ja esta aqui, pulando.
)

echo  [2/6] Preparando o PHP...
where php >nul 2>nul && (
    for /f "delims=" %%p in ('where php') do if not defined SYSPHP set "SYSPHP=%%p"
)
if not exist "%PHP%" (
    if defined SYSPHP (
        "%SYSPHP%" -r "exit(version_compare(PHP_VERSION,'8.2','>=') && extension_loaded('pdo_sqlite') && extension_loaded('mbstring') && extension_loaded('fileinfo') ? 0 : 1);" && set "PHP=%SYSPHP%"
    )
)
if not exist "%PHP%" (
    echo        Baixando PHP portatil, sem instalar nada...
    if not exist "%TOOLS%\php" mkdir "%TOOLS%\php"
    curl -L --fail -o "%TEMP%\php.zip" "%PHP_ZIP%" || (echo [ERRO] Nao consegui baixar o PHP. & goto :fim)
    tar -xf "%TEMP%\php.zip" -C "%TOOLS%\php" || goto :fim
    del "%TEMP%\php.zip"
    (
        echo extension_dir = "%TOOLS%\php\ext"
        echo extension=curl
        echo extension=fileinfo
        echo extension=mbstring
        echo extension=openssl
        echo extension=pdo_sqlite
        echo extension=sqlite3
        echo extension=zip
        echo memory_limit = 512M
        echo upload_max_filesize = 8M
        echo post_max_size = 10M
    ) > "%TOOLS%\php\php.ini"
)
"%PHP%" -v >nul 2>nul || (echo [ERRO] O PHP nao abriu. Instale o "Visual C++ Redistributable 2015-2022 x64" da Microsoft e rode de novo. & goto :fim)

echo  [3/6] Preparando o Composer...
if not exist "%TOOLS%\composer.phar" (
    curl -L --fail -o "%TOOLS%\composer.phar" "%COMPOSER%" || (echo [ERRO] Nao consegui baixar o Composer. & goto :fim)
)

cd /d "%APP%"

echo  [4/6] Instalando as dependencias do Laravel (pode demorar)...
if not exist "vendor\autoload.php" (
    "%PHP%" "%TOOLS%\composer.phar" install --no-dev --no-interaction --prefer-dist --optimize-autoloader || (echo [ERRO] O Composer falhou. & goto :fim)
)

echo  [5/6] Configurando e criando o banco com os dados de exemplo...
if not exist ".env" copy ".env.example" ".env" >nul
findstr /b /c:"APP_KEY=base64" ".env" >nul || "%PHP%" artisan key:generate --force
if not exist "database\database.sqlite" type nul > "database\database.sqlite"
"%PHP%" artisan migrate:fresh --seed --force || (echo [ERRO] Nao consegui criar o banco. & goto :fim)
"%PHP%" artisan optimize:clear >nul

echo  [6/6] Pronto! Abrindo o navegador...
echo.
echo  ==============================================================
echo    Site:   http://localhost:%PORT%
echo    Logins de teste estao em database\seeders\DefaultUserSeeder.php
echo    Para desligar, feche esta janela.
echo  ==============================================================
echo.
start "" "http://localhost:%PORT%"
"%PHP%" artisan serve --host=127.0.0.1 --port=%PORT%

:fim
echo.
pause
