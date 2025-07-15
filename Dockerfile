# PHP 8.1 ve Apache tabanlı resmi PHP görüntüsünü kullan
FROM php:8.1-apache

# Gerekli PHP uzantılarını yükle
# pdo_sqlite varsayılan olarak gelir, pdo_mysql'i yükle
RUN docker-php-ext-install pdo_mysql pdo_sqlite

# Apache mod_rewrite'ı etkinleştir (URL yeniden yazma için)
RUN a2enmod rewrite

# Apache yapılandırmasını kopyala (isteğe bağlı, varsayılan yeterli olabilir)
# COPY apache-conf.conf /etc/apache2/sites-available/000-default.conf

# Uygulama kodunu Apache'nin varsayılan web dizinine kopyala
COPY . /var/www/html/

# Çalışma dizinini ayarla
WORKDIR /var/www/html

# Varsayılan Apache portunu açığa çıkar
EXPOSE 80

# Apache sunucusunu başlat
CMD ["apache2-foreground"]
