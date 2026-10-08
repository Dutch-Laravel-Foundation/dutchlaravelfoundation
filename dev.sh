#!/usr/bin/env bash
set -euo pipefail

: "${APP_URL:?Supply the environment-assigned APP_URL}"
: "${APP_PORT:?Supply the environment-assigned backend port}"
: "${VITE_PORT:?Supply the environment-assigned Vite port}"
: "${INERTIA_SSR_URL:?Supply the environment-assigned SSR URL}"

backend=
frontend=
cleanup() {
    trap - EXIT INT TERM
    for pid in "$backend" "$frontend"; do
        if [[ -n "$pid" ]]; then
            kill "$pid" 2>/dev/null || true
        fi
    done
    for pid in "$backend" "$frontend"; do
        if [[ -n "$pid" ]]; then
            wait "$pid" 2>/dev/null || true
        fi
    done
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

php artisan serve --host=127.0.0.1 --port="$APP_PORT" --no-reload &
backend=$!
bun run dev --host=127.0.0.1 --port="$VITE_PORT" --strictPort &
frontend=$!

# The first process exit ends the session, preserving its exit status.
status=0
wait -n "$backend" "$frontend" || status=$?
exit "$status"
