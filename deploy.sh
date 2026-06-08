#!/usr/bin/env bash
cd "$(dirname "$0")"
export SITE_URL="https://bellacria.com.br"
export PROJECT_DIR="/home2/pensandobem/public_html/bella-criativa"
export PUBLIC_DIR="/home2/pensandobem/public_html/bella-criativa/public"
source "../_deploy.sh"
