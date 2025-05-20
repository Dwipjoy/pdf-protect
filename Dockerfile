# Use official PHP image
FROM php:8.2-apache

# Install dependencies
RUN apt-get update && \
    apt-get install -y qpdf unzip && \
    docker-php-ext-install mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html
