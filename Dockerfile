FROM ridibooks/performance-apache-base:latest
MAINTAINER Kang Ki Tae <kt.kang@ridi.com>

# Install zip, cron and PHP modules (gd, exif)
RUN apt-get update \
&& apt-get install -y \
  zip cron libfreetype6-dev libjpeg62-turbo-dev libpng12-dev libxpm-dev libvpx-dev \
&& docker-php-ext-configure gd \
  --with-freetype-dir=/usr/lib/x86_64-linux-gnu/ \
  --with-jpeg-dir=/usr/lib/x86_64-linux-gnu/ \
  --with-png-dir=/usr/lib/x86_64-linux-gnu/ \
  --with-xpm-dir=/usr/lib/x86_64-linux-gnu/ \
  --with-vpx-dir=/usr/lib/x86_64-linux-gnu/ \
&& docker-php-ext-install gd exif \

# Clean
&& apt-get autoclean -y && apt-get clean -y && rm -rf /var/lib/apt/lists/*

# Set PHP upload size
ADD docs/docker/php/uploads.ini /usr/local/etc/php/conf.d/

# Set cron
ADD docs/docker/cron.d/intranet_crontab /etc/cron.d/

# Enable apache mod and site
ADD docs/docker/apache/*.conf /etc/apache2/sites-available/
RUN a2dissite 000-default && a2ensite intranet

# Change entrypoint
EXPOSE 80 443
ADD docs/docker/docker-entrypoint.sh /
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

ADD . /var/www/html
WORKDIR /var/www/html
RUN make
