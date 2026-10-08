#!/usr/bin/env bash
# Starts the PHP server and Vite (with Inertia SSR). Works with macOS /bin/bash 3.2.
set -euo pipefail

: "${APP_PORT:?Export APP_PORT (PHP server port) before running composer dev}"
: "${VITE_PORT:?Export VITE_PORT (Vite port) before running composer dev}"
export INERTIA_SSR_URL="${INERTIA_SSR_URL:-http://127.0.0.1:$((VITE_PORT + 1))}"

backend=
frontend=
cleanup() {
    trap - EXIT INT TERM
    for pid in $backend $frontend; do
        kill "$pid" 2>/dev/null || true
    done
    for pid in $backend $frontend; do
        wait "$pid" 2>/dev/null || true
    done
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

php artisan serve --host=127.0.0.1 --port="$APP_PORT" --no-reload &
backend=$!
bun run dev --host=127.0.0.1 --port="$VITE_PORT" --strictPort &
frontend=$!

# Bash 3.2 has no `wait -n`: poll until one process exits, then return its status.
while kill -0 "$backend" 2>/dev/null && kill -0 "$frontend" 2>/dev/null; do
    sleep 0.2
done

status=0
if kill -0 "$backend" 2>/dev/null; then
    wait "$frontend" || status=$?
else
    wait "$backend" || status=$?
fi
exit "$status"
