#!/usr/bin/env bash
set -e

# Install PHP 8.2 + required extensions
apt-get update -y
apt-get install -y php8.2 php8.2-cli php8.2-mysql php8.2-curl \
  php8.2-mbstring php8.2-xml php8.2-zip php8.2-gd openssl

# Install Composer dependencies
cd backend
php8.2 composer.phar install --no-dev --optimize-autoloader
cd ..
