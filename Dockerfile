FROM ubuntu:24.04
LABEL maintainer="Brady Wetherington <bwetherington@grokability.com>"

# No need to add `apt-get clean` here, reference:
# - https://github.com/grokability/snipe-it/pull/9201
# - https://docs.docker.com/develop/develop-images/dockerfile_best-practices/#apt-get

RUN export DEBIAN_FRONTEND=noninteractive; \
    export DEBCONF_NONINTERACTIVE_SEEN=true; \
    echo 'tzdata tzdata/Areas select Etc' | debconf-set-selections; \
    echo 'tzdata tzdata/Zones/Etc select UTC' | debconf-set-selections; \
    apt-get update -qqy \
 && apt-get install -qqy --no-install-recommends \
apt-utils \
apache2 \
apache2-bin \
libapache2-mod-php8.3 \
php8.3-curl \
php8.3-ldap \
php8.3-mysql \
php8.3-gd \
php8.3-xml \
php8.3-mbstring \
php8.3-zip \
php8.3-bcmath \
php8.3-redis \
php-memcached \
patch \
curl \
wget  \
vim \
git \
cron \
mysql-client \
supervisor \
cron \
gcc \
make \
autoconf \
libc-dev \
libldap-common \
pkg-config \
php8.3-dev \
ca-certificates \
unzip \
dnsutils \
&& rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


RUN curl -L -O https://github.com/pear/pearweb_phars/raw/master/go-pear.phar
RUN php go-pear.phar

RUN phpenmod gd
RUN phpenmod bcmath

RUN sed -i 's/variables_order = .*/variables_order = "EGPCS"/' /etc/php/8.3/apache2/php.ini
RUN sed -i 's/variables_order = .*/variables_order = "EGPCS"/' /etc/php/8.3/cli/php.ini

RUN useradd -m --uid 10000 --gid 50 docker

RUN echo export APACHE_RUN_USER=docker >> /etc/apache2/envvars
RUN echo export APACHE_RUN_GROUP=staff >> /etc/apache2/envvars

COPY docker/000-default.conf /etc/apache2/sites-enabled/000-default.conf

#SSL
RUN mkdir -p /var/lib/snipeit/ssl
#COPY docker/001-default-ssl.conf /etc/apache2/sites-enabled/001-default-ssl.conf
COPY docker/001-default-ssl.conf /etc/apache2/sites-available/001-default-ssl.conf

RUN a2enmod ssl
RUN a2ensite 001-default-ssl.conf

COPY . /var/www/html

RUN a2enmod rewrite

COPY docker/column-statistics.cnf /etc/mysql/conf.d/column-statistics.cnf

############ INITIAL APPLICATION SETUP #####################

WORKDIR /var/www/html

#Append to bootstrap file (less brittle than 'patch')
# RUN sed -i 's/return $app;/$env="production";\nreturn $app;/' bootstrap/start.php

#copy all configuration files
# COPY docker/*.php /var/www/html/app/config/production/
COPY docker/docker.env /var/www/html/.env

RUN chown -R docker /var/www/html

RUN \
	rm -r "/var/www/html/storage/private_uploads" && ln -fs "/var/lib/snipeit/data/private_uploads" "/var/www/html/storage/private_uploads" \
      && rm -rf "/var/www/html/public/uploads" && ln -fs "/var/lib/snipeit/data/uploads" "/var/www/html/public/uploads" \
      && rm -r "/var/www/html/storage/app/backups" && ln -fs "/var/lib/snipeit/dumps" "/var/www/html/storage/app/backups" \
      && mkdir -p "/var/lib/snipeit/keys" && ln -fs "/var/lib/snipeit/keys/oauth-private.key" "/var/www/html/storage/oauth-private.key" \
      && ln -fs "/var/lib/snipeit/keys/oauth-public.key" "/var/www/html/storage/oauth-public.key" \
      && ln -fs "/var/lib/snipeit/keys/ldap_client_tls.cert" "/var/www/html/storage/ldap_client_tls.cert" \
      && ln -fs "/var/lib/snipeit/keys/ldap_client_tls.key" "/var/www/html/storage/ldap_client_tls.key" \
      && chown docker "/var/lib/snipeit/keys/" \
      && chown -Rh docker "/var/www/html/storage/" \
      && chmod +x /var/www/html/artisan \
      && echo "Finished setting up application in /var/www/html"

############## DEPENDENCIES via COMPOSER ###################

#global install of composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Get dependencies
USER docker
RUN COMPOSER_CACHE_DIR=/dev/null composer install --no-dev --working-dir=/var/www/html && rm -rf /var/www/html/vendor/*/*/.git
USER root

############### APPLICATION INSTALL/INIT #################

#RUN php artisan app:install
# too interactive! Try something else

#COPY docker/app_install.exp /tmp/app_install.exp
#RUN chmod +x /tmp/app_install.exp
#RUN /tmp/app_install.exp

############### DATA VOLUME #################

VOLUME ["/var/lib/snipeit"]

##### START SERVER

COPY docker/startup.sh docker/supervisord.conf /
COPY docker/supervisor-exit-event-listener /usr/bin/supervisor-exit-event-listener
RUN chmod +x /startup.sh /usr/bin/supervisor-exit-event-listener

CMD ["/startup.sh"]

EXPOSE 80
EXPOSE 443
