#!/bin/bash

echo "🛑 Arrêt du conteneur de PROD..."

docker-compose -f docker-compose.prod.yaml -p vide-grenier-prod down

echo "✅ Conteneur de PROD arrêté."