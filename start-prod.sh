#!/bin/bash

# Script de démarrage pour l'environnement de production
echo "🚀 Démarrage de l'environnement PROD..."

# Force le rebuild sans cache - les variables viennent du .env.prod
echo "🔨 Rebuild de l'image PROD..."
docker-compose -p vide-grenier-prod --env-file .env.prod build --no-cache



# Démarrer les conteneurs
echo "🚀 Démarrage des conteneurs PROD..."
docker-compose -p vide-grenier-prod --env-file .env.prod -f docker-compose.yaml up --force-recreate -d

echo "🎉 Environnement PROD démarré sur http://localhost:9089"
