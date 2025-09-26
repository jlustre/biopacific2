@echo off
REM Shortcut for running php artisan commands
REM Change to the directory where this batch file is located
cd /d "%~dp0"
php artisan %*
