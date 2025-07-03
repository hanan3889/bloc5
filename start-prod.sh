#!/bin/bash

echo "🚀 Démarrage de l'environnement PROD..."


git checkout main

git pull origin main

docker-compose -p videgrenier-prod --env-file .env.prod -f docker-compose.prod.yaml up --build -d

echo "🎉 Environnement PROD démarré sur http://localhost:9089"