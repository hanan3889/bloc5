#!/bin/bash

# Script de démarrage pour l'environnement de développement
echo "🚀 Démarrage de l'environnement DEV..."


# Force le rebuild sans cache - les variables viennent du .env.dev
echo "🔨 Rebuild de l'image DEV..."
docker-compose -p vide-grenier-dev --env-file .env.dev build --no-cache


# Démarrage des containers
echo "🚀 Démarrage des containers..."
docker-compose -p vide-grenier-dev --env-file .env.dev -f docker-compose.yaml up --force-recreate -d


echo "🎉 Environnement DEV démarré sur http://localhost:9000"