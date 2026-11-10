#!/usr/bin/env bash
set -euo pipefail

# Track whether the deployment has successfully entered maintenance mode.
MAINTENANCE_ENABLED=0

# Report failed deployment commands so the operator knows the release did not finish.
on_exit() {
    # Read the command's status before executing anything else in the exit trap.
    local status=$?
    # Remove the temporary bypass cookie jar so it cannot be reused after deployment.
    if [ -n "${HEALTH_COOKIE_JAR:-}" ]; then
        rm -f "$HEALTH_COOKIE_JAR"
    fi
    # Warn on failure so a nonzero deployment cannot look successful.
    if [ "$status" -ne 0 ]; then
        # Explain whether maintenance remains active to avoid serving an unhealthy release.
        if [ "$MAINTENANCE_ENABLED" -eq 1 ]; then
            printf 'Deployment failed with exit code %s; the application remains in maintenance mode. Inspect logs before recovery.\n' "$status" >&2
        else
            printf 'Deployment failed with exit code %s before maintenance mode was enabled. Inspect logs before retrying.\n' "$status" >&2
        fi
    fi
}

# Ensure failures anywhere in the deployment are reported when the script exits.
trap on_exit EXIT

# Select the local health endpoint by default; HEALTH_URL must point to the Laravel /up route.
HEALTH_URL="${HEALTH_URL:-http://127.0.0.1/up}"

# Derive the application origin so the maintenance bypass can be set for the health request.
HEALTH_ORIGIN="${HEALTH_URL%/up}"

# Generate a one-time bypass secret without printing it to deployment logs.
HEALTH_SECRET=$(php -r 'echo bin2hex(random_bytes(32));')

# Create a private temporary cookie jar for the maintenance-mode health check.
HEALTH_COOKIE_JAR=$(mktemp)

# Enable maintenance mode while allowing only the health check with its one-time bypass secret.
php artisan down --retry=60 --secret="$HEALTH_SECRET"

# Record that maintenance is active so failures leave the application closed to normal traffic.
MAINTENANCE_ENABLED=1

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

# Obtain Laravel's maintenance bypass cookie without following its redirect.
curl -sS -c "$HEALTH_COOKIE_JAR" -o /dev/null "${HEALTH_ORIGIN}/${HEALTH_SECRET}"

# Request the health endpoint with the bypass cookie and capture its HTTP status.
HTTP_STATUS=$(curl -sS -b "$HEALTH_COOKIE_JAR" -o /dev/null -w '%{http_code}' "$HEALTH_URL")

# Fail deployments for non-HTTP responses or status codes outside the accepted 200-399 range.
if [[ ! "$HTTP_STATUS" =~ ^[0-9]{3}$ ]] || (( 10#$HTTP_STATUS < 200 || 10#$HTTP_STATUS >= 400 )); then
    # Print the failed endpoint and response code so the operator can diagnose the deployment.
    printf 'Health check failed for %s (HTTP %s).\n' "$HEALTH_URL" "$HTTP_STATUS" >&2
    # Return failure so automation does not mark an unhealthy deployment successful.
    exit 1
fi

# Leave maintenance mode only after the newly deployed application passes its health check.
php artisan up

# Record that the application has left maintenance mode successfully.
MAINTENANCE_ENABLED=0

# Report the successful health check status to the operator.
printf 'Health check passed for %s (HTTP %s).\n' "$HEALTH_URL" "$HTTP_STATUS"
