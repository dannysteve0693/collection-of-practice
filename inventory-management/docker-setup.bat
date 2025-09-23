@echo off
echo 🐳 Setting up Laravel Inventory Management with Docker...

REM Create necessary directories
echo 📁 Creating necessary directories...
if not exist "storage\app\public" mkdir storage\app\public
if not exist "storage\framework\cache\data" mkdir storage\framework\cache\data
if not exist "storage\framework\sessions" mkdir storage\framework\sessions
if not exist "storage\framework\views" mkdir storage\framework\views
if not exist "storage\logs" mkdir storage\logs
if not exist "bootstrap\cache" mkdir bootstrap\cache

REM Copy environment file
echo ⚙️ Setting up environment configuration...
if not exist ".env" (
    copy .env.docker .env
    echo ✅ Environment file created from .env.docker
)

REM Build and start containers
echo 🔨 Building Docker containers...
docker-compose up -d --build

REM Wait for database to be ready
echo ⏳ Waiting for database to be ready...
timeout /t 30 /nobreak

REM Install Composer dependencies
echo 📦 Installing Composer dependencies...
docker-compose exec app composer install --no-dev --optimize-autoloader

REM Generate application key
echo 🔑 Generating application key...
docker-compose exec app php artisan key:generate

REM Run database migrations and seeders
echo 🗃️ Running database migrations...
docker-compose exec app php artisan migrate:fresh --seed

REM Create storage link
echo 🔗 Creating storage link...
docker-compose exec app php artisan storage:link

REM Cache configuration for better performance
echo ⚡ Caching configuration...
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

REM Build frontend assets
echo 🎨 Building frontend assets...
docker-compose exec node npm install
docker-compose exec node npm run build

echo.
echo 🎉 Docker setup complete!
echo.
echo 🌐 Application URL: http://localhost:8080
echo 🗄️ phpMyAdmin URL: http://localhost:8081
echo.
echo 👥 Test User Credentials:
echo    Admin: admin@example.com / password
echo    Purchaser: purchaser@example.com / password
echo    Sales: sales@example.com / password
echo    Viewer: viewer@example.com / password
echo.
echo 📊 Database Info:
echo    Host: localhost:3306
echo    Database: laravel
echo    Username: laravel
echo    Password: laravel
echo.
echo 🛠️ Useful Commands:
echo    Start: docker-compose up -d
echo    Stop: docker-compose down
echo    Logs: docker-compose logs -f
echo    Shell: docker-compose exec app bash
echo.
pause