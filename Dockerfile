FROM php:8.2-apache

# Aqueta part executa les seguents linies dintre del contenidor, el que fa es intal·lar les dependencies del sistema, 
# les extensions de PHP i el soport HTTPS en Apache.
RUN apt-get update \
    && apt-get install -y --no-install-recommends openssl \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers ssl \
    && printf 'ServerName 192.168.221.32\n' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && mkdir -p /etc/apache2/ssl \
    && openssl req -x509 -nodes -days 365 \
        -newkey rsa:2048 \
        -keyout /etc/apache2/ssl/cas3-selfsigned.key \
        -out /etc/apache2/ssl/cas3-selfsigned.crt \
        -subj "/C=ES/ST=Tarragona/L=Amposta/O=CAS3/OU=IAW/CN=192.168.221.32" \
        -addext "subjectAltName=IP:192.168.221.32" \
    && a2ensite default-ssl

# Aqui definim el directori de treball d'Apache.
WORKDIR /var/www/html

# Copiem els arxius de configuració d'apache a dintre del contenidor per poder aplicar-los.
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY apache/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
