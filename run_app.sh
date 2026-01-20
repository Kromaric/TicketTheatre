#!/bin/bash

# Chemins absolus pour la base de données partagée
DB_PATH=$(pwd)/core-service/database/database.sqlite

echo "--- Préparation de la base de données partagée ---"
touch "$DB_PATH"

setup_backend_service() {
    local dir=$1
    local port=$2
    local db=$3

    echo "--- Configuration de $dir ---"
    cd "$dir" || exit
    composer install --ignore-platform-reqs

    if [ ! -f .env ]; then
        cp .env.example .env
        php artisan key:generate
    fi

    # Mise à jour du chemin de la base de données dans le .env
    sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=sqlite|g" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$db|g" .env

    # Migration et seed selon le service
    if [[ "$dir" == "core-service" ]]; then
        php artisan migrate:fresh --seed
    else
        php artisan migrate
    fi

    echo "--- Lancement de $dir sur le port $port ---"
    php artisan serve --port="$port" &
    cd ..
}

# 1. Lancer les services Backend
setup_backend_service "auth-service" 8081 "$DB_PATH"
setup_backend_service "core-service" 8082 "$DB_PATH"
setup_backend_service "payment-service" 8083 "$(pwd)/payment-service/database/database.sqlite"

# 2. Lancer le Frontend
echo "--- Configuration du Frontend ---"
cd frontend || exit
npm install
echo "--- Lancement du Frontend ---"
npm run dev &
cd ..

echo "------------------------------------------------"
echo "Tous les services sont en cours de lancement..."
echo "Auth Service    : http://localhost:8081"
echo "Core Service    : http://localhost:8082"
echo "Payment Service : http://localhost:8083"
echo "Frontend        : http://localhost:5173"
echo "------------------------------------------------"
echo "Appuyez sur Ctrl+C pour tout arrêter."
wait