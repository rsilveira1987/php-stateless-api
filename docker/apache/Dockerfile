FROM php:7.4-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
        dnsutils \
        vim \
        libzip-dev \
        libonig-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install redis && docker-php-ext-enable redis

RUN docker-php-ext-configure gd \
        --with-freetype=/usr/include/ \
        --with-jpeg=/usr/include/

# Add and Enable PHP-PDO Extenstions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    zip \
    mbstring \
    gd \
    zip

RUN docker-php-ext-enable pdo_mysql mbstring gd zip

# Use the default production configuration
# RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY ./session.ini /usr/local/etc/php/conf.d/

# Install PHP Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Remove Cache
RUN rm -rf /var/cache/apk/*

# RUN apt-get update && apt-get install -y \
#         libpng-dev \
#         libonig-dev \
#         libwebp-dev \
#         libzip-dev \
#         libjpeg62-turbo-dev \
#         libpng-dev libxpm-dev \
#         libfreetype6-dev \
#     && rm -rf /var/lib/apt/lists/*
        
# RUN docker-php-ext-install pdo pdo_mysql zip\
#     && docker-php-ext-configure gd \
#         --with-freetype=/usr/include/freetype2 \
#         # --with-png=/usr/include \
#         --with-jpeg=/usr/include \
#     && docker-php-ext-install gd \
#     && docker-php-ext-install mbstring \
#     && docker-php-ext-enable gd

# Copy conf files
COPY ./ports.conf /etc/apache2/ports.conf
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

# Configure apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV APACHE_RUN_USER=www-data
ENV APACHE_RUN_GROUP=www-data
ENV APACHE_LOG_DIR=/var/log/apache2
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www

# Update composer
# RUN composer --working-dir ./public update --no-interaction 

# Add UID '1000' to www-data
# RUN usermod -u 1000 www-data

# Change current user to www
# USER www-data

# CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
CMD ["/bin/bash", "-c", "/usr/sbin/apache2ctl -D FOREGROUND"]
  