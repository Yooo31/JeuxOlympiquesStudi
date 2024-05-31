# Dockerfile
FROM php:8.3-apache

# Installation des dépendances PHP nécessaires
RUN apt-get update && \
    apt-get install -y \
        libicu-dev \
        zlib1g-dev \
        libzip-dev \
        unzip \
        libpng-dev \
	&& docker-php-ext-install pdo_mysql intl zip gd

RUN apt-get install -y \
        libfreetype-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd

# Activer les modules Apache nécessaires pour Symfony
RUN a2enmod rewrite

# Configuration du vhost d'Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Répertoire de travail
WORKDIR /var/www/html

# Copie de l'application Symfony dans le conteneur
COPY . .

# Installation de Composer et des dépendances Symfony
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-scripts --no-autoloader

# Exposer le port 80
EXPOSE 80

# Commande par défaut pour lancer Apache
CMD ["apache2-foreground"]