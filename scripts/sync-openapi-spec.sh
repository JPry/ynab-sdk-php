#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

SPEC_URL="${SPEC_URL:-https://api.ynab.com/papi/open_api_spec.yaml}"
TARGET_PATH="${TARGET_PATH:-${REPO_ROOT}/openapi/ynab-v1.openapi.yaml}"

TMP_FILE="$(mktemp)"
cleanup() {
	rm -f "${TMP_FILE}"
}
trap cleanup EXIT

curl -fsSL "${SPEC_URL}" -o "${TMP_FILE}"

if [[ ! -s "${TMP_FILE}" ]]; then
	echo "Downloaded OpenAPI spec is empty."
	exit 1
fi

extract_version() {
	local file_path="$1"
	awk -F': ' '/^  version:/{print $2; exit}' "${file_path}"
}

downloaded_version="$(extract_version "${TMP_FILE}")"

if [[ -f "${TARGET_PATH}" ]] && cmp -s "${TARGET_PATH}" "${TMP_FILE}"; then
	echo "OpenAPI spec unchanged (version: ${downloaded_version:-unknown})."
	exit 0
fi

mv "${TMP_FILE}" "${TARGET_PATH}"
echo "OpenAPI spec updated at ${TARGET_PATH} (version: ${downloaded_version:-unknown})."
