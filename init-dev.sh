#!/bin/bash

set -e

echo "🔑 Generating JWT keys..."

if [ ! -f backend/config/jwt/private.pem ]; then
    docker-compose exec php sh -c "
        mkdir -p config/jwt && \
        openssl genrsa -out config/jwt/private.pem -aes256 -passout pass:\$JWT_PASSPHRASE 4096 && \
        openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem -passin pass:\$JWT_PASSPHRASE && \
        chmod 600 config/jwt/private.pem && \
        chmod 644 config/jwt/public.pem && \
        chown www-data:www-data config/jwt/private.pem /var/www/html/config/jwt/public.pem
    "
    echo "✅ JWT keys generated"
else
    echo "ℹ️  JWT keys already exist, skipping generation"
fi

echo "📦 Installing dependencies..."

docker-compose exec php composer install --no-interaction

echo "🗄️  Initializing database..."

docker-compose exec php php bin/console doctrine:database:create --if-not-exists

echo "🔄 Running migrations..."

docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction

echo "🧪 Initializing test database..."

cd backend && ./init-test-db.sh && cd ..

echo "✅ Development environment initialized successfully!"
