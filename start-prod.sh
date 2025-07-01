#!/bin/bash

echo "🚀 Démarrage de l'environnement PROD..."

# docker-compose -f docker-compose.prod.yaml -p vide-grenier-prod up -d web-prod db-prod

# echo "🎉 Environnement PROD démarré sur http://videgrenier-prod:9089"

git checkout main

git pull origin main

docker-compose -p videgrenier-prod --env-file .env.prod -f docker-compose.prod.yaml up --build -d
echo "🎉 Environnement PROD démarré sur http://videgrenier-prod:9089"