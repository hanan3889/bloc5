#!/bin/bash

echo "🛑 Arrêt du conteneur de PROD..."

docker-compose -f docker-compose.prod.yaml -p bloc5 down

echo "✅ Conteneur de PROD arrêté."