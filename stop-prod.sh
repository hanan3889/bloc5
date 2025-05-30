#!/bin/bash
echo "Stopping prod containers..."
docker compose --env-file .env.prod --profile prod stop
echo "Prod containers stopped."