#!/bin/bash

# Script de démarrage pour l'environnement de production
echo "🚀 Démarrage de l'environnement DEV..."

docker compose --env-file .env.dev -p videgrenier_dev up -d --build

echo "🎉 Environnement DEV demarré !"