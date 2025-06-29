#!/bin/bash


echo "🛑 Arrêt de l'environnement DEV..."

docker-compose -f docker-compose.dev.yaml -p vide-grenier-dev down

echo "✅ Environnement DEV arrêté."

