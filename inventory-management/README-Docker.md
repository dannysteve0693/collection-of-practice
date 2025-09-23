# 🐳 Docker Setup for Laravel Inventory Management System

This Docker configuration provides a complete development environment for the Laravel 11 Inventory Management System with PHP 8.2, MySQL 8.0, Nginx, Redis, and phpMyAdmin.

## 📋 Prerequisites

- Docker Desktop (Windows/Mac) or Docker Engine (Linux)
- Docker Compose v2.0+
- Git

## 🚀 Quick Start

### Windows Users
```bash
./docker-setup.bat
```

### Linux/Mac Users
```bash
chmod +x docker-setup.sh
./docker-setup.sh
```

### Manual Setup
```bash
# 1. Clone and navigate to the project
git clone <repository-url>
cd inventory-management

# 2. Copy environment file
cp .env.docker .env

# 3. Start containers
docker-compose up -d --build

# 4. Install dependencies and setup
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate:fresh --seed
docker-compose exec app php artisan storage:link

# 5. Build assets
docker-compose exec node npm install
docker-compose exec node npm run build
```

## 🌐 Access URLs

| Service | URL | Description |
|---------|-----|-------------|
| **Laravel App** | http://localhost:8080 | Main application |
| **phpMyAdmin** | http://localhost:8081 | Database management |
| **MySQL** | localhost:3306 | Direct database access |
| **Redis** | localhost:6379 | Cache/Session storage |

## 👥 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@example.com | password |
| **Purchaser** | purchaser@example.com | password |
| **Sales** | sales@example.com | password |
| **Viewer** | viewer@example.com | password |

## 📊 Database Information

- **Host**: `localhost` (or `db` from within containers)
- **Port**: `3306`
- **Database**: `laravel`
- **Username**: `laravel`
- **Password**: `laravel`
- **Root Password**: `root`

## 🐳 Docker Services

### Application Stack
```yaml
app:        # PHP 8.2-FPM with Laravel
web:        # Nginx web server
db:         # MySQL 8.0
redis:      # Redis for caching/sessions
phpmyadmin: # Database administration
node:       # Node.js for asset compilation
```

### Container Details

| Container | Image | Ports | Purpose |
|-----------|-------|--------|---------|
| `laravel_app` | Custom PHP 8.2 | - | Laravel application |
| `laravel_web` | nginx:alpine | 8080:80 | Web server |
| `laravel_db` | mysql:8.0 | 3306:3306 | Database |
| `laravel_redis` | redis:alpine | 6379:6379 | Cache/Sessions |
| `laravel_phpmyadmin` | phpmyadmin/phpmyadmin | 8081:80 | DB Admin |
| `laravel_node` | node:18-alpine | - | Asset compilation |

## 🛠️ Useful Commands

### Container Management
```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Rebuild containers
docker-compose up -d --build

# View logs
docker-compose logs -f
docker-compose logs -f app  # Specific service

# Access application shell
docker-compose exec app bash

# Access database shell
docker-compose exec db mysql -u laravel -p laravel
```

### Laravel Commands
```bash
# Artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:cache

# Composer commands
docker-compose exec app composer install
docker-compose exec app composer update

# NPM commands
docker-compose exec node npm install
docker-compose exec node npm run dev
docker-compose exec node npm run build
```

### Database Operations
```bash
# Fresh migration with seeding
docker-compose exec app php artisan migrate:fresh --seed

# Backup database
docker-compose exec db mysqldump -u laravel -plaravel laravel > backup.sql

# Restore database
docker-compose exec -T db mysql -u laravel -plaravel laravel < backup.sql
```

## 🔧 Configuration Files

### Docker Configuration
- `docker-compose.yml` - Main Docker Compose configuration
- `Dockerfile` - PHP application container
- `docker/nginx/nginx.conf` - Nginx web server configuration
- `docker/php/local.ini` - PHP configuration
- `docker/mysql/my.cnf` - MySQL configuration

### Laravel Configuration
- `.env.docker` - Docker environment template
- `config/database.php` - Database configuration
- `config/cache.php` - Cache configuration

## 🔍 Troubleshooting

### Common Issues

**Port conflicts:**
```bash
# Check what's using the ports
netstat -tulpn | grep :8080
netstat -tulpn | grep :3306

# Change ports in docker-compose.yml if needed
```

**Permission issues:**
```bash
# Fix Laravel permissions
docker-compose exec app chown -R www-data:www-data /var/www/storage
docker-compose exec app chmod -R 775 /var/www/storage
```

**Database connection issues:**
```bash
# Check if database is ready
docker-compose exec db mysql -u laravel -plaravel -e "SHOW DATABASES;"

# Reset database
docker-compose exec app php artisan migrate:fresh --seed
```

**Clear all caches:**
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Performance Optimization

**Enable OPcache** (already configured in `docker/php/local.ini`):
```ini
opcache.enable=1
opcache.memory_consumption=128
```

**Database tuning** (configured in `docker/mysql/my.cnf`):
```ini
innodb_buffer_pool_size=256M
```

### Development vs Production

For **production**, update:
- Change `APP_ENV=production` in `.env`
- Set `APP_DEBUG=false`
- Use stronger `APP_KEY`
- Configure proper `APP_URL`
- Use production database credentials
- Enable HTTPS in Nginx config

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Docker Compose Reference](https://docs.docker.com/compose/)
- [MySQL 8.0 Reference](https://dev.mysql.com/doc/refman/8.0/en/)
- [Nginx Configuration](https://nginx.org/en/docs/)

## 🐛 Support

If you encounter issues:
1. Check the container logs: `docker-compose logs -f`
2. Verify all services are running: `docker-compose ps`
3. Ensure ports are not in use by other applications
4. Try rebuilding containers: `docker-compose up -d --build`

## 🔄 Updates

To update the application:
```bash
git pull origin main
docker-compose down
docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php artisan migrate
docker-compose exec node npm install && npm run build
```