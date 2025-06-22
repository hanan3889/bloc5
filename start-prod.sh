#!/bin/bash

# Script de démarrage pour l'environnement de production
# echo "🚀 Démarrage de l'environnement PROD..."

# Force le rebuild sans cache - les variables viennent du .env.prod
# echo "🔨 Rebuild de l'image PROD..."
echo "🚀 Démarrage des conteneurs PROD..."
# docker-compose -p vide-grenier-prod --env-file .env.prod build --no-cache

docker-compose --env-file .env.prod -f docker-compose.yaml -p vide-grenier-prod up -d

# Démarrer les conteneurs

# docker-compose -p vide-grenier-prod --env-file .env.prod -f docker-compose.yaml up --force-recreate -d

echo "🎉 Environnement PROD démarré sur http://localhost:9089"
