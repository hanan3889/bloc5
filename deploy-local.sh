#!/bin/bash

echo "🚀 Déploiement local de la prod"

# Facultatif : pull les dernières modifs si tu suis un repo distant
# git pull origin main

echo "🧼 Arrêt des anciens conteneurs"
docker-compose -f
 docker-compose.prod.yml down

echo "🔁 Reconstruction des conteneurs de prod"
docker-compose -f docker-compose.prod.yml up -d --build --force-recreate

echo "✅ Déploiement terminé : conteneur prod lancé"

echo "📡 Accès au site via http://localhost:9089"