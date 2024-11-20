FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    cron \
    libzip-dev \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libftp-dev \
    libgd-dev \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd ftp zip pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Copy Apache configuration file
COPY public/000-default.conf /etc/apache2/sites-available/000-default.conf

# Create necessary directories with appropriate permissions
RUN mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP dependencies (should be done after copying the application files to leverage Docker cache)
RUN composer install --no-dev --optimize-autoloader

# Copy Supervisor configuration file
COPY supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Expose port 80
EXPOSE 80

# Start Supervisor (to manage Apache and other services)
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
