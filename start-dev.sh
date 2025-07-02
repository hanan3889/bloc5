#!/bin/bash

echo "🚀 Démarrage de l'environnement DEV..."

docker-compose -f docker-compose.dev.yaml -p vide-grenier-dev up -d web-dev db-dev

echo "🎉 Environnement DEV démarré sur http://localhost:8000"
