#!/bin/bash

echo "🛑 Arrêt de l'environnement PROD..."

docker-compose -f docker-compose.yaml -p vide-grenier-prod down

echo "✅ Environnement PROD arrêté."
