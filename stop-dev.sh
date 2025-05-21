#!/bin/bash

echo "Stopping dev containers..."
docker compose --env-file .env.dev --profile dev down
echo "Dev containers stopped."
