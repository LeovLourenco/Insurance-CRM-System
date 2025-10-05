FROM node:18-alpine AS frontend-builder

WORKDIR /app

COPY package*.json webpack.mix.js ./
COPY resources/ ./resources/
COPY public/ ./public/

RUN npm ci && npm run production

# ============================================
FROM php:8.2-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP essenciais
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite

# Configurar Document Root para /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar código da aplicação
COPY . .

# Instalar dependências do Composer
RUN composer install --no-dev --optimize-autoloader

# IMPORTANTE: Copiar assets compilados DEPOIS do COPY . .
COPY --from=frontend-builder /app/public/css ./public/css
COPY --from=frontend-builder /app/public/js ./public/js
COPY --from=frontend-builder /app/public/mix-manifest.json ./public/mix-manifest.json

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expor porta 80
EXPOSE 80

# Variáveis de ambiente padrão
ENV APP_ENV=production
ENV APP_DEBUG=false

# Comando para iniciar Apache
CMD ["apache2-foreground"]