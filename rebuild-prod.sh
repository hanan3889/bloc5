#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

echo "🚀 Nettoyage complet de l'environnement PROD avant reconstruction..."

# Arrêter et supprimer les services spécifiques pour s'assurer qu'ils sont bien libérés
docker-compose -f docker-compose.prod.yaml stop web-prod db-prod || true
docker rm -f videgrenier-web-prod videgrenier-db-prod || true

# Supprimer les réseaux et volumes orphelins du projet
docker-compose -f docker-compose.prod.yaml down --remove-orphans --volumes || true

echo "🚀 Reconstruction de l'image Docker PROD (web-prod)..."
docker-compose -f docker-compose.prod.yaml build --no-cache web-prod

echo "🚀 Arrêt des conteneurs PROD avant redémarrage..."
docker-compose -f docker-compose.prod.yaml stop web-prod db-prod || true

echo "🚀 Démarrage de l'environnement PROD avec la nouvelle image..."
docker-compose -f docker-compose.prod.yaml up -d web-prod db-prod

echo "✅ Environnement PROD mis à jour et démarré sur http://videgrenier-prod:9089"
echo "
Si les changements ne sont pas visibles, veuillez vider le cache de votre navigateur (Ctrl+F5 ou Cmd+Shift+R)."
