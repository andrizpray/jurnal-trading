#!/bin/bash
# ============================================================
#  Trading Journal Pro — Auto Installer
#  Jalankan: bash install.sh
# ============================================================

set -e

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

log()  { echo -e "${GREEN}✅ $1${NC}"; }
warn() { echo -e "${YELLOW}⚠️  $1${NC}"; }
info() { echo -e "${CYAN}ℹ️  $1${NC}"; }
err()  { echo -e "${RED}❌ $1${NC}"; exit 1; }

echo ""
echo -e "${BOLD}${CYAN}╔══════════════════════════════════════════╗${NC}"
echo -e "${BOLD}${CYAN}║   Trading Journal Pro — Auto Installer   ║${NC}"
echo -e "${BOLD}${CYAN}╚══════════════════════════════════════════╝${NC}"
echo ""

# ── Check requirements ──────────────────────────────────────
info "Checking requirements..."
command -v php     >/dev/null 2>&1 || err "PHP not found. Install PHP 8.2+"
command -v composer>/dev/null 2>&1 || err "Composer not found. Install from getcomposer.org"
command -v node    >/dev/null 2>&1 || err "Node.js not found. Install Node.js 18+"
command -v npm     >/dev/null 2>&1 || err "NPM not found."
log "All requirements satisfied."

# ── Check if running inside existing Laravel project ────────
if [ ! -f "composer.json" ]; then
    err "composer.json not found. Run this script from the project root directory."
fi

# ── Create .env ─────────────────────────────────────────────
if [ ! -f ".env" ]; then
    info "Creating .env from .env.example..."
    cp .env.example .env
    log ".env created."
else
    warn ".env already exists, skipping."
fi

# ── Install PHP deps ─────────────────────────────────────────
info "Installing PHP dependencies..."
composer install --no-interaction --prefer-dist 2>/dev/null
log "PHP dependencies installed."

# ── Generate app key ─────────────────────────────────────────
info "Generating application key..."
php artisan key:generate
log "App key generated."

# ── Database prompt ──────────────────────────────────────────
echo ""
echo -e "${BOLD}Database Configuration${NC}"
read -p "  DB Host     [127.0.0.1]: " DB_HOST;   DB_HOST=${DB_HOST:-127.0.0.1}
read -p "  DB Port     [3306]:      " DB_PORT;   DB_PORT=${DB_PORT:-3306}
read -p "  DB Database [trading_journal]: " DB_NAME; DB_NAME=${DB_NAME:-trading_journal}
read -p "  DB Username [root]:      " DB_USER;   DB_USER=${DB_USER:-root}
read -s -p "  DB Password:             " DB_PASS;  echo ""

# Update .env
sed -i.bak "s/^DB_HOST=.*/DB_HOST=${DB_HOST}/"         .env
sed -i.bak "s/^DB_PORT=.*/DB_PORT=${DB_PORT}/"         .env
sed -i.bak "s/^DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" .env
sed -i.bak "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" .env
sed -i.bak "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" .env
rm -f .env.bak
log "Database config updated."

# ── Run migrations ────────────────────────────────────────────
info "Running migrations..."
php artisan migrate --seed --force
log "Database migrated & seeded."

# ── Install & build frontend ──────────────────────────────────
info "Installing frontend dependencies..."
npm install --silent
log "Node modules installed."

info "Building frontend assets..."
npm run build
log "Frontend assets built."

# ── Storage link ──────────────────────────────────────────────
php artisan storage:link 2>/dev/null || true

# ── Done ──────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}╔══════════════════════════════════════════╗${NC}"
echo -e "${BOLD}${GREEN}║         Installation Complete! 🎉        ║${NC}"
echo -e "${BOLD}${GREEN}╚══════════════════════════════════════════╝${NC}"
echo ""
echo -e "  Run:  ${CYAN}php artisan serve${NC}"
echo -e "  URL:  ${CYAN}http://localhost:8000${NC}"
echo ""
echo -e "  Demo: ${YELLOW}demo@cuanhunters.com${NC} / ${YELLOW}password${NC}"
echo ""
