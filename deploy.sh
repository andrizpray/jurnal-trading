#!/bin/bash

# Trading Journal Pro Deployment Script
# Usage: ./deploy.sh [production|staging]

set -e

ENVIRONMENT=${1:-production}
APP_NAME="trading-journal-pro"
APP_DIR="/var/www/$APP_NAME"
BACKUP_DIR="/var/backups/$APP_NAME"
DATE=$(date +%Y%m%d_%H%M%S)

echo "🚀 Deploying Trading Journal Pro to $ENVIRONMENT"
echo "=============================================="

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Please run as root or with sudo"
    exit 1
fi

# Create directories if they don't exist
mkdir -p $APP_DIR $BACKUP_DIR

# Backup current version
if [ -d "$APP_DIR" ]; then
    echo "📦 Backing up current version..."
    tar -czf "$BACKUP_DIR/backup_$DATE.tar.gz" -C $(dirname $APP_DIR) $(basename $APP_DIR)
    echo "✅ Backup created: $BACKUP_DIR/backup_$DATE.tar.gz"
fi

# Clone or pull latest code
if [ -d "$APP_DIR/.git" ]; then
    echo "🔄 Pulling latest changes..."
    cd $APP_DIR
    git pull origin main
else
    echo "📥 Cloning repository..."
    git clone https://github.com/yourusername/$APP_NAME.git $APP_DIR
    cd $APP_DIR
fi

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "📦 Installing Node.js dependencies..."
npm ci --only=production

# Build assets
echo "🔨 Building frontend assets..."
npm run build

# Setup environment
if [ ! -f "$APP_DIR/.env" ]; then
    echo "⚙️  Creating environment file..."
    cp $APP_DIR/.env.example $APP_DIR/.env
    php artisan key:generate
    echo "⚠️  Please edit $APP_DIR/.env with your configuration"
fi

# Database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Cache optimizations
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR/storage $APP_DIR/bootstrap/cache

# Restart services
echo "🔄 Restarting web server..."
systemctl reload nginx 2>/dev/null || systemctl reload apache2 2>/dev/null || true

# Setup queue worker (if using queues)
if [ "$ENVIRONMENT" = "production" ]; then
    echo "👷 Setting up queue worker..."
    systemctl enable --now $APP_NAME-worker 2>/dev/null || echo "⚠️  Queue service not configured"
fi

# Setup cron job for scheduler
echo "⏰ Setting up cron job..."
(crontab -l 2>/dev/null | grep -v "$APP_DIR/artisan schedule:run"; echo "* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo "=============================================="
echo "🎉 Deployment completed successfully!"
echo ""
echo "📊 Application Info:"
echo "   URL: https://yourdomain.com"
echo "   Directory: $APP_DIR"
echo "   Environment: $ENVIRONMENT"
echo ""
echo "🔧 Next steps:"
echo "   1. Configure SSL certificate"
echo "   2. Setup email in .env for notifications"
echo "   3. Test the application"
echo "   4. Monitor logs: tail -f $APP_DIR/storage/logs/laravel.log"
echo ""
echo "📞 For support, check README.md or open an issue on GitHub"