FROM php:8.2-cli

# Install SNMP extension and tools
RUN apt-get update && apt-get install -y \
    libsnmp-dev \
    snmp \
    unzip \
    git \
    && docker-php-ext-install snmp \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy all application files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader

CMD ["vendor/bin/phpunit"]
