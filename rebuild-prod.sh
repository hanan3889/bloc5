#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

echo "🚀 Arrêt et suppression de l'environnement PROD si en cours d'exécution..."
# Stop and remove the web-prod service if it's running
docker-compose -f docker-compose.prod.yaml stop web-prod || true
docker-compose -f docker-compose.prod.yaml rm -f web-prod || true

echo "🚀 Reconstruction de l'image Docker PROD (web-prod)..."
docker-compose -f docker-compose.prod.yaml build web-prod

echo "🚀 Démarrage de l'environnement PROD avec la nouvelle image..."
docker-compose -f docker-compose.prod.yaml up -d web-prod

echo "✅ Environnement PROD mis à jour et démarré sur http://videgrenier-prod:9089"
