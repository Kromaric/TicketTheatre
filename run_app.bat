@echo off
set DB_PATH=%cd%\core-service\database\database.sqlite

echo --- Preparation de la base de donnee partagée ---
if not exist "%DB_PATH%" type nul > "%DB_PATH%"

echo --- Lancement de Auth Service (8081) ---
start "Auth Service" cmd /k "cd auth-service && composer install --ignore-platform-reqs && php artisan serve --port=8081"

echo --- Lancement de Core Service (8082) ---
start "Core Service" cmd /k "cd core-service && composer install --ignore-platform-reqs && php artisan migrate:fresh --seed && php artisan serve --port=8082"

echo --- Lancement de Payment Service (8083) ---
start "Payment Service" cmd /k "cd payment-service && composer install --ignore-platform-reqs && if not exist database\database.sqlite type nul > database\database.sqlite && php artisan migrate && php artisan serve --port=8083"

echo --- Lancement du Frontend (Vite) ---
start "Frontend" cmd /k "cd frontend && npm install && npm run dev"

echo ------------------------------------------------
echo Tous les services ont ete lances.
echo Backend : Ports 8081, 8082, 8083
echo Frontend : Port 5173 (par defaut)
echo ------------------------------------------------
pause