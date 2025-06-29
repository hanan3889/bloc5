#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

echo "🔄 Redémarrage de l'environnement PROD..."

# Check if docker-compose is installed
if ! command -v docker-compose &> /dev/null
then
    echo "Erreur: docker-compose n'est pas installé. Veuillez l'installer pour continuer."
    exit 1
fi

# Stop and remove existing containers and networks for a clean start
echo "Nettoyage des conteneurs PROD existants..."
if ! docker-compose -f docker-compose.yaml -p vide-grenier-prod down --remove-orphans; then
    echo "Avertissement: Impossible de nettoyer complètement les conteneurs PROD existants. Tentative de redémarrage quand même."
fi

# Restart the specified services in detached mode
echo "Démarrage des services web-prod et db-prod..."
if docker-compose -f docker-compose.prod.yaml -p vide-grenier-prod up -d web-prod db-prod; then
    echo "✅ Environnement PROD redémarré sur http://videgrenier-prod:9089"
else
    echo "❌ Erreur lors du redémarrage de l'environnement PROD."
    exit 1
fi
