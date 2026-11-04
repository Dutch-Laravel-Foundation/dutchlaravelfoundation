#!/usr/bin/env bash
set -euo pipefail

for name in DEPLOY_HOST DEPLOY_USER DEPLOY_PORT DEPLOY_SSH_PRIVATE_KEY DEPLOY_SSH_KNOWN_HOSTS; do
    if [[ -z "${!name:-}" ]]; then
        echo "::error::Missing production secret: $name" >&2
        exit 1
    fi
done

# These values become SSH configuration, so reject config directives/newlines.
if [[ ! "$DEPLOY_HOST" =~ ^[a-zA-Z0-9._:-]+$ ]] ||
   [[ ! "$DEPLOY_USER" =~ ^[a-zA-Z0-9_][a-zA-Z0-9_-]*$ ]] ||
   [[ ! "$DEPLOY_PORT" =~ ^[0-9]{1,5}$ ]] ||
   (( 10#$DEPLOY_PORT < 1 || 10#$DEPLOY_PORT > 65535 )); then
    echo '::error::Invalid deployment host, user, or port.' >&2
    exit 1
fi

umask 077
mkdir -p "$HOME/.ssh"
chmod 700 "$HOME/.ssh"
printf '%s\n' "$DEPLOY_SSH_PRIVATE_KEY" > "$HOME/.ssh/dlf-deploy"
printf '%s\n' "$DEPLOY_SSH_KNOWN_HOSTS" > "$HOME/.ssh/dlf-known-hosts"

# Validate without connecting or printing key material. CI needs an unencrypted key.
ssh-keygen -y -P '' -f "$HOME/.ssh/dlf-deploy" > /dev/null
ssh-keygen -l -f "$HOME/.ssh/dlf-known-hosts" > /dev/null

{
    printf 'Host dlf-production\n'
    printf '  HostName %s\n  User %s\n  Port %s\n' "$DEPLOY_HOST" "$DEPLOY_USER" "$DEPLOY_PORT"
    printf '  IdentityFile "%s/.ssh/dlf-deploy"\n' "$HOME"
    printf '  UserKnownHostsFile "%s/.ssh/dlf-known-hosts"\n' "$HOME"
    printf '%s\n' \
        '  IdentitiesOnly yes' \
        '  BatchMode yes' \
        '  StrictHostKeyChecking yes' \
        '  ForwardAgent no' \
        '  ClearAllForwardings yes' \
        '  ConnectTimeout 15' \
        '  ServerAliveInterval 30' \
        '  ServerAliveCountMax 3'
} > "$HOME/.ssh/config"
