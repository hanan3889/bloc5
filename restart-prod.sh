echo "🔄 Redémarrage de l'environnement PROD..."

# Stop + clean
docker-compose --env-file .env.prod -f docker-compose.yaml -p vide-grenier-prod down

# Restart
docker-compose --env-file .env.prod -f docker-compose.yaml -p vide-grenier-prod up -d

echo "✅ Environnement PROD redémarré : http://localhost:9089"