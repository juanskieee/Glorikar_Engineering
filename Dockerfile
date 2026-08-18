FROM php:8.2-apache

# Install system dependencies first, then PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    curl \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mysqli zip mbstring xml curl \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP dependencies
RUN cd backend && composer install --no-dev --optimize-autoloader
