#!/bin/bash

echo "🚀 Démarrage de l'environnement DEV..."

# docker start videgrenier-web-dev videgrenier-db-dev
docker-compose --env-file .env.dev -f docker-compose.yaml -p vide-grenier-dev up -d

echo "🎉 Environnement DEV demarré sur http://localhost:9000"
