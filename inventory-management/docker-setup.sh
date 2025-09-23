#!/bin/bash

echo "🐳 Setting up Laravel Inventory Management with Docker..."

# Create necessary directories
echo "📁 Creating necessary directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Copy environment file
echo "⚙️ Setting up environment configuration..."
if [ ! -f .env ]; then
    cp .env.docker .env
    echo "✅ Environment file created from .env.docker"
fi

# Build and start containers
echo "🔨 Building Docker containers..."
docker-compose up -d --build

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 30

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
docker-compose exec app composer install --no-dev --optimize-autoloader

# Generate application key
echo "🔑 Generating application key..."
docker-compose exec app php artisan key:generate

# Run database migrations and seeders
echo "🗃️ Running database migrations..."
docker-compose exec app php artisan migrate:fresh --seed

# Create storage link
echo "🔗 Creating storage link..."
docker-compose exec app php artisan storage:link

# Set proper permissions
echo "🔐 Setting proper permissions..."
docker-compose exec app chown -R www-data:www-data /var/www/storage
docker-compose exec app chown -R www-data:www-data /var/www/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/storage
docker-compose exec app chmod -R 775 /var/www/bootstrap/cache

# Cache configuration for better performance
echo "⚡ Caching configuration..."
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Build frontend assets
echo "🎨 Building frontend assets..."
docker-compose exec node npm install
docker-compose exec node npm run build

echo ""
echo "🎉 Docker setup complete!"
echo ""
echo "🌐 Application URL: http://localhost:8080"
echo "🗄️ phpMyAdmin URL: http://localhost:8081"
echo ""
echo "👥 Test User Credentials:"
echo "   Admin: admin@example.com / password"
echo "   Purchaser: purchaser@example.com / password"
echo "   Sales: sales@example.com / password"
echo "   Viewer: viewer@example.com / password"
echo ""
echo "📊 Database Info:"
echo "   Host: localhost:3306"
echo "   Database: laravel"
echo "   Username: laravel"
echo "   Password: laravel"
echo ""
echo "🛠️ Useful Commands:"
echo "   Start: docker-compose up -d"
echo "   Stop: docker-compose down"
echo "   Logs: docker-compose logs -f"
echo "   Shell: docker-compose exec app bash"
echo ""