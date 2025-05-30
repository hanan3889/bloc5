#!/bin/bash

echo "Stopping dev containers..."
docker compose --env-file .env.dev --profile dev stop
echo "Dev containers stopped."