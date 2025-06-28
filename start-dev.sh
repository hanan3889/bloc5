#!/bin/bash

echo "🚀 Démarrage de l'environnement DEV..."

docker-compose -f docker-compose.yaml -p vide-grenier-dev up -d web-dev db-dev

echo "🎉 Environnement DEV demarré sur http://videgrenier-dev"