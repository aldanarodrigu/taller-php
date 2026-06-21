#!/usr/bin/env bash
# configure-github-secrets.sh
# Sets all required GitHub Actions secrets in BOTH repos using the GitHub CLI.
#
# Prerequisites:
#   1. gh CLI installed and authenticated: gh auth login
#   2. Terraform already applied: terraform -chdir=infra/terraform apply
#   3. Run this script from the root of the taller-php repo.
#
# Usage:
#   chmod +x deploy/aws/configure-github-secrets.sh
#   ./deploy/aws/configure-github-secrets.sh
set -euo pipefail

BACKEND_REPO=""
FRONTEND_REPO=""

# ── Auto-detect repos from git remote ────────────────────────────────────────
if command -v git >/dev/null 2>&1; then
  BACKEND_REPO=$(git -C "$(dirname "$0")/../.." remote get-url origin 2>/dev/null \
    | sed -E 's|https://github.com/||;s|git@github.com:||;s|\.git$||' || true)
fi

echo "=== GitHub Secrets Setup ==="
echo ""
echo "Reading Terraform outputs..."
TF_DIR="$(dirname "$0")/../../infra/terraform"

if ! command -v terraform >/dev/null 2>&1; then
  echo "ERROR: terraform CLI not found. Install it from https://developer.hashicorp.com/terraform/install"
  exit 1
fi

if ! command -v gh >/dev/null 2>&1; then
  echo "ERROR: gh CLI not found. Install it from https://cli.github.com"
  exit 1
fi

# Read outputs (sensitive ones need -json)
TF_OUTPUTS=$(terraform -chdir="${TF_DIR}" output -json)

AWS_KEY_ID=$(echo "$TF_OUTPUTS"       | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['github_actions_key_id']['value'])")
AWS_SECRET=$(echo "$TF_OUTPUTS"       | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['github_actions_secret']['value'])")
AWS_REGION=$(terraform -chdir="${TF_DIR}" output -raw aws_region 2>/dev/null || echo "us-east-2")
INSTANCE_ID=$(echo "$TF_OUTPUTS"      | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['ec2_instance_id']['value'])")
S3_BUCKET=$(echo "$TF_OUTPUTS"        | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['s3_bucket']['value'])")
CF_ID=$(echo "$TF_OUTPUTS"            | python3 -c "import sys,json; d=json.load(sys.stdin); print(d['cloudfront_id']['value'])")
BACKEND_URL=$(terraform -chdir="${TF_DIR}" output -raw backend_url)
CF_DOMAIN=$(terraform -chdir="${TF_DIR}" output -raw cloudfront_domain)

# ── Prompt for repos if not detected ─────────────────────────────────────────
if [ -z "${BACKEND_REPO}" ]; then
  read -rp "Backend GitHub repo (owner/repo): " BACKEND_REPO
fi
read -rp "Frontend GitHub repo (owner/repo) [${FRONTEND_REPO:-}]: " input
FRONTEND_REPO="${input:-${FRONTEND_REPO}}"
if [ -z "${FRONTEND_REPO}" ]; then
  echo "ERROR: frontend repo is required"
  exit 1
fi

# ── Prompt for secrets not in Terraform ──────────────────────────────────────
read -rp  "REVERB_APP_KEY (e.g. your-prod-key): " REVERB_KEY
read -rsp "REVERB_APP_SECRET: " REVERB_SECRET
echo ""
read -rp  "GOOGLE_CLIENT_ID (leave empty to skip): " GOOGLE_ID
read -rsp "GOOGLE_CLIENT_SECRET (leave empty to skip): " GOOGLE_SECRET
echo ""
read -rsp "MAIL_PASSWORD (leave empty to skip): " MAIL_PASS
echo ""
read -rp  "MAIL_USERNAME (leave empty to skip): " MAIL_USER

# ── Set backend secrets ───────────────────────────────────────────────────────
echo ""
echo "Setting backend secrets on ${BACKEND_REPO}..."

set_secret() {
  local repo="$1" name="$2" value="$3"
  if [ -n "${value}" ]; then
    gh secret set "${name}" --repo "${repo}" --body "${value}"
    echo "  ✓ ${name}"
  fi
}

set_secret "${BACKEND_REPO}" "AWS_ACCESS_KEY_ID"      "${AWS_KEY_ID}"
set_secret "${BACKEND_REPO}" "AWS_SECRET_ACCESS_KEY"  "${AWS_SECRET}"
set_secret "${BACKEND_REPO}" "AWS_REGION"             "${AWS_REGION}"
set_secret "${BACKEND_REPO}" "DEPLOY_SSM_INSTANCE_ID" "${INSTANCE_ID}"
set_secret "${BACKEND_REPO}" "DEPLOY_PATH"            "/var/www/taller-php"

# ── Set frontend secrets ──────────────────────────────────────────────────────
echo ""
echo "Setting frontend secrets on ${FRONTEND_REPO}..."

set_secret "${FRONTEND_REPO}" "AWS_ACCESS_KEY_ID"     "${AWS_KEY_ID}"
set_secret "${FRONTEND_REPO}" "AWS_SECRET_ACCESS_KEY" "${AWS_SECRET}"
set_secret "${FRONTEND_REPO}" "AWS_REGION"            "${AWS_REGION}"
set_secret "${FRONTEND_REPO}" "S3_BUCKET"             "${S3_BUCKET}"
set_secret "${FRONTEND_REPO}" "CLOUDFRONT_ID"         "${CF_ID}"
set_secret "${FRONTEND_REPO}" "VITE_APP_URL"          "${BACKEND_URL}"
set_secret "${FRONTEND_REPO}" "VITE_API_URL"          "${BACKEND_URL}/api"
set_secret "${FRONTEND_REPO}" "VITE_REVERB_KEY"       "${REVERB_KEY}"
set_secret "${FRONTEND_REPO}" "VITE_REVERB_HOST"      "$(echo "${BACKEND_URL}" | sed -E 's|https://||')"
set_secret "${FRONTEND_REPO}" "VITE_REVERB_PORT"      "6001"
set_secret "${FRONTEND_REPO}" "VITE_REVERB_SCHEME"    "https"

# ── Optional secrets ─────────────────────────────────────────────────────────
set_secret "${BACKEND_REPO}" "GOOGLE_CLIENT_ID"     "${GOOGLE_ID}"
set_secret "${BACKEND_REPO}" "GOOGLE_CLIENT_SECRET" "${GOOGLE_SECRET}"
set_secret "${BACKEND_REPO}" "MAIL_PASSWORD"        "${MAIL_PASS}"
set_secret "${BACKEND_REPO}" "MAIL_USERNAME"        "${MAIL_USER}"

echo ""
echo "=== Done ==="
echo ""
echo "Backend URL:    ${BACKEND_URL}"
echo "Frontend URL:   ${CF_DOMAIN}"
echo ""
echo "Next steps:"
echo "  1. Run bootstrap-app.sh on the EC2 instance (once)."
echo "  2. Push to main on both repos to trigger CI/CD."
