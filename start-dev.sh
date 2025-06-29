#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

echo "🚀 Démarrage de l'environnement DEV..."

# Check if docker-compose is installed
if ! command -v docker-compose &> /dev/null
then
    echo "Erreur: docker-compose n'est pas installé. Veuillez l'installer pour continuer."
    exit 1
fi

# Stop and remove existing containers and networks for a clean start
# --remove-orphans: Remove containers for services not defined in the Compose file
# --volumes: Uncomment if you want to remove named volumes (e.g., database data) for a fresh start
echo "Nettoyage des conteneurs DEV existants..."
if ! docker-compose -f docker-compose.yaml -p vide-grenier-dev down --remove-orphans; then
    echo "Avertissement: Impossible de nettoyer complètement les conteneurs DEV existants. Tentative de démarrage quand même."
fi

# Start the specified services in detached mode
echo "Démarrage des services web-dev et db-dev..."
if docker-compose -f docker-compose.dev.yaml -p vide-grenier-dev up -d web-dev db-dev; then
    echo "Redémarrage du service Apache dans le conteneur DEV..."
    docker-compose -f docker-compose.dev.yaml exec web-dev apachectl restart || true
    echo "🎉 Environnement DEV démarré sur http://videgrenier-dev:8000"
else
    echo "❌ Erreur lors du démarrage de l'environnement DEV."
    exit 1
fi
