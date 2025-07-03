# #!/bin/bash


echo "🚀 Démarrage de l'environnement DEV..."

# Charger les variables d'environnement depuis .env.dev
if [ -f .env.dev ]; then
  echo "📦 Chargement des variables depuis .env.dev"
  set -a
  source .env.dev
  set +a
else
  echo "⚠️  Fichier .env.dev introuvable. Assure-toi qu'il est bien présent à la racine du projet."
  exit 1
fi

# Supprimer les anciens conteneurs si existants
echo "🧹 Suppression des anciens conteneurs existants..."
docker rm -f videgrenier-web-dev videgrenier-db-dev 2>/dev/null

# Lancer les services avec docker-compose

docker-compose -p videgrenier-dev --env-file .env.dev -f docker-compose.dev.yaml up --build -d
echo "🎉 Environnement DEV démarré sur http://localhost:$APP_PORT"
