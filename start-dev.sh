#!/bin/bash

# Script de démarrage pour l'environnement de dev

echo "🚀 Démarrage de l'environnement DEV..."

docker compose --env-file .env.dev --profile dev up -d --build

echo "🎉 Environnement DEV demarré sur http://localhost:9000"