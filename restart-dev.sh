echo "🔄 Redémarrage de l'environnement DEV..."

# Stop + clean
docker-compose --env-file .env.dev -f docker-compose.yaml -p vide-grenier-dev down

# Restart
docker-compose --env-file .env.dev -f docker-compose.yaml -p vide-grenier-dev up -d

echo "✅ Environnement DEV redémarré : http://localhost:9000"