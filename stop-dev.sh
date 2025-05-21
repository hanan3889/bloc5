#!/bin/bash

ENV_FILE=".env.dev"
PROJECT_NAME="dev"

echo "Stopping production environment..."

docker compose --env-file "$ENV_FILE" -p "$PROJECT_NAME" down
