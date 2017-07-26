FROM php:7.1-apache
MAINTAINER Kang Ki Tae <kt.kang@ridi.com>

RUN docker-php-source extract \

# Install common
&& apt-get update \
&& apt-get install wget software-properties-common vim git zlib1g-dev libmcrypt-dev libldap2-dev cron -y \
&& docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu \
&& docker-php-ext-install ldap pdo zip pdo_mysql \

# Install node && bower
&& curl -sL https://deb.nodesource.com/setup_6.x | bash - \
&& apt-get install nodejs -y \
&& npm install -g bower \

# Install composer
&& curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /usr/bin/composer \

# Install couchbase php extention
&& wget http://packages.couchbase.com/ubuntu/couchbase.key && apt-key add couchbase.key && rm couchbase.key  \
&& add-apt-repository 'deb http://packages.couchbase.com/ubuntu trusty trusty/main' \
&& apt-get update \
&& apt-get install -y build-essential libcouchbase2-core libcouchbase-dev libcouchbase2-bin libcouchbase2-libevent \
&& pecl install couchbase-2.2.3 \
&& docker-php-ext-enable couchbase \

# Install xdebug php extention
&& pecl install xdebug \
&& docker-php-ext-enable xdebug\
&& echo "xdebug.remote_enable=on\n"\
        "xdebug.remote_autostart=off\n" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \

# Clean
&& apt-get autoclean -y && apt-get clean -y && rm -rf /var/lib/apt/lists/* \
&& docker-php-source delete

# Set php upload size
ADD docs/docker/php/uploads.ini /usr/local/etc/php/conf.d/

# Set cron
ADD docs/docker/cron.d/intranet_crontab /etc/cron.d/

# Enable apache mod and site
ADD docs/docker/apache/*.conf /etc/apache2/sites-available/
RUN a2enmod rewrite \
&& a2dissite 000-default \
&& a2ensite intranet

# Change entrypoint
EXPOSE 80 443
ADD docs/docker/docker-entrypoint.sh /
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

ADD . /var/www/html
WORKDIR /var/www/html
RUN make

VOLUME ["/var/www/html"]
