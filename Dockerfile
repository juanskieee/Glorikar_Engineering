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

# Apache config — allow .htaccess overrides + show PHP errors temporarily
RUN echo 'ServerName glorikar-engineering.onrender.com\n\
<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    php_flag display_errors on\n\
    php_value error_reporting 32767\n\
</Directory>' > /etc/apache2/conf-available/glorikar.conf \
&& a2enconf glorikar

# Stream Apache error log to stdout so Render shows it in logs
RUN ln -sf /dev/stderr /var/log/apache2/error.log

EXPOSE 80
