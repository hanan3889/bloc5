#!/bin/bash

# Script de démarrage pour l'environnement de production
echo "🚀 Démarrage de l'environnement PROD..."

docker compose --env-file .env.prod --profile prod up -d --build

echo "🎉 Environnement PRO demarré sur http://localhost:9089"