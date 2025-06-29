#!/bin/bash

echo "🚀 Reconstruction de l'image Docker PROD (web-prod)..."
docker-compose build web-prod

echo "✅ Image PROD reconstruite."
