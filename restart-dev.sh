#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

echo "🔄 Redémarrage de l'environnement DEV..."

# Check if docker-compose is installed
if ! command -v docker-compose &> /dev/null
then
    echo "Erreur: docker-compose n'est pas installé. Veuillez l'installer pour continuer."
    exit 1
fi

# Stop and remove existing containers and networks for a clean start
echo "Nettoyage des conteneurs DEV existants..."
if ! docker-compose -f docker-compose.yaml -p vide-grenier-dev down --remove-orphans; then
    echo "Avertissement: Impossible de nettoyer complètement les conteneurs DEV existants. Tentative de redémarrage quand même."
fi

# Restart the specified services in detached mode
echo "Démarrage des services web-dev et db-dev..."
if docker-compose -f docker-compose.dev.yaml -p vide-grenier-dev up -d web-dev db-dev; then
    echo "✅ Environnement DEV redémarré sur http://videgrenier-dev:8000"
else
    echo "❌ Erreur lors du redémarrage de l'environnement DEV."
    exit 1
fi
