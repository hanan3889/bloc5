#!/bin/bash

ENV_FILE=".env.prod"
PROJECT_NAME="videgrenier_prod"

echo "Stopping production environment..."

docker compose --env-file "$ENV_FILE" -p "$PROJECT_NAME" down
