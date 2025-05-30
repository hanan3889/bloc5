#!/bin/bash

# Script de démarrage pour l'environnement de production
echo "🚀 Démarrage de l'environnement PROD..."

docker-compose -p vide-grenier-prod --env-file .env.prod -f docker-compose.yaml up --build --force-recreate -d

echo "🎉 Environnement PROD démarré sur http://localhost:9089"
