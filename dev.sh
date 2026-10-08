#!/usr/bin/env bash
set -euo pipefail

if (( BASH_VERSINFO[0] < 4 || (BASH_VERSINFO[0] == 4 && BASH_VERSINFO[1] < 3) )); then
    printf '%s\n' 'composer dev requires Bash 4.3+ (wait -n). Install a current Bash and put its bin directory first in PATH; see README.md for macOS setup.' >&2
    exit 1
fi

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
