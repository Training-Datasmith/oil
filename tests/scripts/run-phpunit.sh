#!/usr/bin/env bash
set -euo pipefail
IMG="${PHP_IMAGE:-php@sha256:42ffbc0798e4449bbd1e14fc4dcb87774aa1ad1900a09ef6a965bc0880aa2161}"
ORDER="${1:-default}"
SEED="${2:-}"
EXTRA=()
if [[ "$ORDER" == "random" ]]; then
  EXTRA+=(--order-by=random --random-order-seed="${SEED:-20261008}")
else
  EXTRA+=(--order-by=default)
fi
docker run --rm -v /workspace:/app -w /app "$IMG" bash -lc '
  sed -i "s/deb.debian.org/archive.debian.org/g" /etc/apt/sources.list 2>/dev/null || true
  sed -i "s/security.debian.org/archive.debian.org/g" /etc/apt/sources.list 2>/dev/null || true
  sed -i "/buster-updates/d" /etc/apt/sources.list 2>/dev/null || true
  apt-get update -qq && DEBIAN_FRONTEND=noninteractive apt-get install -y -qq zip libzip-dev >/dev/null 2>&1 || true
  docker-php-ext-install zip >/dev/null 2>&1 || true
  php -d error_reporting=-1 vendor/bin/phpunit '"${EXTRA[*]}"'
'
