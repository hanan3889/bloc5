#!/bin/bash

# Script de démarrage pour l'environnement de production
echo "🚀 Démarrage de l'environnement PROD..."

docker compose --env-file .env.prod -p videgrenier_prod up --build -d

echo "🎉 Environnement PROD demarré !"