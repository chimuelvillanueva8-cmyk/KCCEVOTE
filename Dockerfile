# ============================================================
# Dockerfile for CodeIgniter 4 (Render, Railway, Koyeb, Fly.io)
# ============================================================
FROM php:8.2-apache

# Install system dependencies and PHP extension libraries
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    git \
    zip \
    unzip \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    intl \
    mbstring \
    mysqli \
    pdo \
    pdo_mysql \
    zip \
    gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Enable Apache rewrite module
RUN a2enmod rewrite

# Configure PHP error logging to stderr for Render logs
RUN echo "log_errors = On\nerror_log = /dev/stderr" > /usr/local/etc/php/conf.d/logging.ini

# Configure Apache DocumentRoot to CodeIgniter's /public folder
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure directory permissions and .htaccess overrides
RUN echo '<Directory /var/www/html/public>' > /etc/apache2/conf-available/codeigniter.conf \
    && echo '    Options -Indexes +FollowSymLinks' >> /etc/apache2/conf-available/codeigniter.conf \
    && echo '    AllowOverride All' >> /etc/apache2/conf-available/codeigniter.conf \
    && echo '    Require all granted' >> /etc/apache2/conf-available/codeigniter.conf \
    && echo '</Directory>' >> /etc/apache2/conf-available/codeigniter.conf \
    && a2enconf codeigniter

# Configure Apache to listen on Render's dynamic PORT environment variable (defaults to 80)
ENV PORT=80
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Install composer dependencies if not already present
RUN if [ ! -d "/var/www/html/vendor" ] || [ ! -f "/var/www/html/vendor/autoload.php" ]; then \
        composer install --no-dev --optimize-autoloader --no-interaction; \
    fi

# Ensure writable directories exist and have proper permissions for Apache user
RUN mkdir -p /var/www/html/writable/cache \
    && mkdir -p /var/www/html/writable/logs \
    && mkdir -p /var/www/html/writable/session \
    && mkdir -p /var/www/html/writable/uploads \
    && chown -R www-data:www-data /var/www/html/writable /var/www/html/public/uploads \
    && chmod -R 775 /var/www/html/writable /var/www/html/public/uploads

# Expose default HTTP port
EXPOSE 80 10000

# Start Apache in the foreground
CMD ["apache2-foreground"]

