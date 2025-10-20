#!/usr/bin/env bash
set -euo pipefail

VERSION_TAG="$1"

if [ -z "$VERSION_TAG" ]; then
  echo "Usage: scripts/check-release.sh vMAJOR.MINOR.PATCH"
  exit 1
fi

if ! grep -q "## \[$VERSION_TAG\]" CHANGELOG.md; then
  echo "Changelog entry for $VERSION_TAG not found."
  exit 2
fi

echo "Changelog entry for $VERSION_TAG found."
