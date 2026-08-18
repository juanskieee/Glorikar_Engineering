FROM php:8.2-apache

# Enable required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN apt-get update && apt-get install -y \
    libzip-dev libonig-dev libxml2-dev curl unzip \
    && docker-php-ext-install zip mbstring xml curl \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install Composer dependencies
RUN cd backend && php composer.phar install --no-dev --optimize-autoloader

# Apache config — allow .htaccess overrides
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/glorikar.conf \
&& a2enconf glorikar

EXPOSE 80
