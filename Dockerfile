# Use the official PHP image with Apache.
FROM php:8.2-apache

# Install system dependencies and php-bcmath.
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install bcmath \
    && rm -rf /var/lib/apt/lists/*

# Set the working directory to /var/www/html.
WORKDIR /var/www/html

# Copy the current directory contents into the container at /var/www/html.
COPY . /var/www/html

# Install Composer.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Composer dependencies.
RUN composer install --no-dev --optimize-autoloader

# Enable Apache mod_rewrite.
RUN a2enmod rewrite

# Expose port 80.
EXPOSE 80

# Start Apache in the foreground.
CMD ["apache2-foreground"]
