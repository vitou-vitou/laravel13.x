#!/usr/bin/env sh
# Local vulnerability scan for the dashboard-v1 image (Risk #4).
# Builds the image, then scans with Trivy (preferred) or Docker Scout.
# Usage: sh scripts/scan-image.sh
#
# Windows note: under Git Bash, MSYS rewrites the /var/run/docker.sock mount
# path and the containerized Trivy fallback fails. Run with path conversion
# off:  MSYS_NO_PATHCONV=1 sh scripts/scan-image.sh
# (Native Linux/macOS and the CI workflow are unaffected.)
set -eu

IMAGE="${IMAGE:-dashboard-v1:scan}"
SEVERITY="${SEVERITY:-HIGH,CRITICAL}"

echo "[scan] building ${IMAGE}…"
docker build -t "$IMAGE" .

if command -v trivy >/dev/null 2>&1; then
  echo "[scan] trivy image ${IMAGE} (severity ${SEVERITY})"
  exec trivy image --ignore-unfixed --severity "$SEVERITY" "$IMAGE"
fi

# Trivy via container if not installed locally
if docker image inspect aquasec/trivy:latest >/dev/null 2>&1 || docker pull aquasec/trivy:latest >/dev/null 2>&1; then
  echo "[scan] trivy (containerized) image ${IMAGE}"
  exec docker run --rm \
    -v /var/run/docker.sock:/var/run/docker.sock \
    aquasec/trivy:latest image --ignore-unfixed --severity "$SEVERITY" "$IMAGE"
fi

if docker scout version >/dev/null 2>&1; then
  echo "[scan] docker scout cves ${IMAGE}"
  exec docker scout cves --only-severity "$(echo "$SEVERITY" | tr 'A-Z,' 'a-z,')" "$IMAGE"
fi

echo "[scan] no scanner available (install trivy or enable docker scout)" >&2
exit 1
