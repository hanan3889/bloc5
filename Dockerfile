# Dockerfile
FROM debian:11

# Define build arguments for environment and Apache config file
# These defaults will be used if not explicitly passed during docker build
ARG APP_ENV=dev
ARG APACHE_CONF=dev.conf


# Set environment variable for use in later RUN commands and at runtime
ENV APP_ENV=${APP_ENV}

# --- System Dependencies & PHP Repository ---
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    lsb-release apt-transport-https ca-certificates wget gnupg2 curl unzip git && \
    rm -rf /var/lib/apt/lists/*

# Add Sury PHP repository (modern way)
RUN wget -qO - https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /usr/share/keyrings/deb.sury.org-php.gpg && \
    echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list

# --- PHP, Apache, and Extensions ---
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    apache2 \
    php8.4 \
    libapache2-mod-php8.4 \
    composer \
    php8.4-mysql \
    php8.4-xml \
    php8.4-mbstring \
    php8.4-curl \
    php8.4-zip \
    php8.4-intl \
    php8.4-gd \
    php8.4-dom && \
    # Conditional installation for dev environment only
    if [ "$APP_ENV" = "dev" ]; then \
        apt-get install -y --no-install-recommends php-pear; \
    fi && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# --- Apache Configuration ---
RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy Apache virtual host configuration based on APACHE_CONF build arg
COPY apache/${APACHE_CONF} /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default.conf

# --- Application Copy & Dependencies ---
COPY . /var/www/html
WORKDIR /var/www/html

# Install PHP dependencies based on APP_ENV
RUN if [ "$APP_ENV" = "prod" ]; then \
        composer install --no-dev --optimize-autoloader; \
    else \
        composer install; \
    fi

    
# --- Permissions ---
RUN chown -R www-data:www-data /var/www/html

# --- Exposure & Command ---
EXPOSE 80

CMD ["apachectl", "-D", "FOREGROUND"]