#!/usr/bin/env bash
set -euo pipefail

# Report failed deployment commands so the operator knows the release did not finish.
on_exit() {
    # Read the command's status before executing anything else in the exit trap.
    local status=$?
    # Warn on failure so a nonzero deployment cannot look successful.
    if [ "$status" -ne 0 ]; then
        # Include the failure code but never expose environment credentials.
        printf 'Deployment failed with exit code %s; inspect the logs before retrying.\n' "$status" >&2
    fi
}

# Ensure failures anywhere in the deployment are reported when the script exits.
trap on_exit EXIT

# Enable maintenance mode while the application code and dependencies are updated.
php artisan down --retry=60

# Fetch the latest production code; omitting this leaves the deployed checkout stale.
git pull origin main

# Install locked production dependencies; omitting this can leave PHP code without required packages.
composer install --no-dev --optimize-autoloader --no-interaction

# Rebuild frontend assets from the lockfile; omitting this leaves production assets outdated.
npm ci && npm run build

# Apply schema changes without interactive confirmation; omitting this can leave code and schema incompatible.
php artisan migrate --force

# Ensure the public storage link exists; this is non-fatal when it already exists.
php artisan storage:link || true

# Clear stale caches before regenerating caches from the newly deployed code and configuration.
php artisan optimize:clear

# Cache configuration for production; omitting it can reduce performance and retain stale configuration.
php artisan config:cache

# Cache route definitions for production; omitting it can reduce performance and leave route behavior stale.
php artisan route:cache

# Cache rendered Blade templates; omitting it can increase response time and retain stale views.
php artisan view:cache

# Cache event/listener discovery; omitting it can leave event mappings stale.
php artisan event:cache

# Ask long-lived queue workers to reload application code after finishing their current job.
php artisan queue:restart

# Leave maintenance mode only after code, schema, assets, and caches are ready.
php artisan up

# Select the local health endpoint by default; HEALTH_URL can target a different deployment endpoint.
HEALTH_URL="${HEALTH_URL:-http://127.0.0.1/up}"

# Request the health endpoint and capture its HTTP status for the final deployment check.
HTTP_STATUS=$(curl -sS -o /dev/null -w '%{http_code}' "$HEALTH_URL")

# Fail deployments for non-HTTP responses or status codes outside the accepted 200-399 range.
if [[ ! "$HTTP_STATUS" =~ ^[0-9]{3}$ ]] || (( 10#$HTTP_STATUS < 200 || 10#$HTTP_STATUS >= 400 )); then
    # Print the failed endpoint and response code so the operator can diagnose the deployment.
    printf 'Health check failed for %s (HTTP %s).\n' "$HEALTH_URL" "$HTTP_STATUS" >&2
    # Return failure so automation does not mark an unhealthy deployment successful.
    exit 1
fi

# Report the successful health check status to the operator.
printf 'Health check passed for %s (HTTP %s).\n' "$HEALTH_URL" "$HTTP_STATUS"
