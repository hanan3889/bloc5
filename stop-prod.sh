#!/bin/bash
echo "Stopping prod containers..."
docker compose --env-file .env.prod --profile prod down
echo "Prod containers stopped."