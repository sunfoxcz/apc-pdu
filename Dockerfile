FROM php:8.2-cli

# Install SNMP extension, SSH2 extension and tools
RUN apt-get update && apt-get install -y \
    libsnmp-dev \
    libssh2-1-dev \
    snmp \
    unzip \
    git \
    && docker-php-ext-install snmp \
    && pecl install ssh2-1.4.1 \
    && docker-php-ext-enable ssh2 \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy all application files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader

CMD ["vendor/bin/phpunit"]
