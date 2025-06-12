#!/bin/bash
echo "Stopping prod containers..."

# Arrêtez les conteneurs directement par leurs noms
docker stop videgrenier-web-prod videgrenier-db-prod

echo "🛑 Prod containers stopped."
