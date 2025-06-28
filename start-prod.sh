#!/bin/bash

echo "🚀 Démarrage de l'environnement PROD..."

docker-compose -f docker-compose.yaml -p vide-grenier-prod up -d web-prod db-prod

echo "🎉 Environnement PROD demarré sur http://videgrenier:8080"
