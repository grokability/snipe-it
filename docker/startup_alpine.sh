#!/bin/sh

# Cribbed from nextcloud docker official repo
# https://github.com/nextcloud/docker/blob/master/docker-entrypoint.sh
# usage: file_env VAR [DEFAULT]
#    ie: file_env 'XYZ_DB_PASSWORD' 'example'
# (will allow for "$XYZ_DB_PASSWORD_FILE" to fill in the value of
#  "$XYZ_DB_PASSWORD" from a file, especially for Docker's secrets feature)
file_env() {
    local var="$1"
    local fileVar="${var}_FILE"
    local def="${2:-}"
    local varValue=$(env | grep -E "^${var}=" | sed -E -e "s/^${var}=//")
    local fileVarValue=$(env | grep -E "^${fileVar}=" | sed -E -e "s/^${fileVar}=//")
    if [ -n "${varValue}" ] && [ -n "${fileVarValue}" ]; then
        echo >&2 "error: both $var and $fileVar are set (but are exclusive)"
        exit 1
    fi
    if [ -n "${varValue}" ]; then
        export "$var"="${varValue}"
    elif [ -n "${fileVarValue}" ]; then
        export "$var"="$(cat "${fileVarValue}")"
    elif [ -n "${def}" ]; then
        export "$var"="$def"
    fi
    unset "$fileVar"
}

# Add docker secrets support for the variables below:
file_env APP_KEY
file_env DB_HOST
file_env DB_PORT
file_env DB_DATABASE
file_env DB_USERNAME
file_env DB_PASSWORD
file_env REDIS_HOST
file_env REDIS_PASSWORD
file_env REDIS_PORT
file_env MAIL_HOST
file_env MAIL_PORT
file_env MAIL_USERNAME
file_env MAIL_PASSWORD

# fix key if needed
if [ -z "$APP_KEY" -a -z "$APP_KEY_FILE" ]
then
  echo "Please re-run this container with an environment variable \$APP_KEY"
  echo "An example APP_KEY you could use is: "
  php artisan key:generate --show
  exit
fi

#if [ ! -f /var/lib/snipeit/ssl/snipeit-ssl.crt -o ! -f /var/lib/snipeit/ssl/snipeit-ssl.key ]
#then
 # rm /etc/apache2/conf.d/ssl.conf && rm /etc/apache2/conf.d/default-ssl.conf
#fi

# create data directories
for dir in \
  'data/private_uploads' \
  'data/uploads/accessories' \
  'data/uploads/avatars' \
  'data/uploads/barcodes' \
  'data/uploads/categories' \
  'data/uploads/companies' \
  'data/uploads/components' \
  'data/uploads/consumables' \
  'data/uploads/departments' \
  'data/uploads/locations' \
  'data/uploads/maintenances' \
  'data/uploads/manufacturers' \
  'data/uploads/models' \
  'data/uploads/suppliers' \
  'dumps' \
  'keys'
do
  [ ! -d "/var/lib/snipeit/$dir" ] && mkdir -p "/var/lib/snipeit/$dir"
done

# seed the bundled default avatar into the uploads volume on first start
# (public/uploads is symlinked to the volume), but never overwrite a
# default that an admin already uploaded
if [ ! -f /var/lib/snipeit/data/uploads/default.png ]; then
  cp /var/www/html/public/img/default.png /var/lib/snipeit/data/uploads/default.png
fi

chown -R apache:root /var/lib/snipeit/data/*
chown -R apache:root /var/lib/snipeit/dumps
chown -R apache:root /var/lib/snipeit/keys
chown -R apache:root /var/www/html/storage/framework/cache

# Fix php settings
if [ ! -z "${PHP_UPLOAD_LIMIT}" ]
then
    find /etc -type f -name php.ini | while IFS= read -r ini; do
        echo "Changing upload limit to ${PHP_UPLOAD_LIMIT}M in $ini"
        sed -i \
            -e "s/^;\? *upload_max_filesize *=.*/upload_max_filesize = ${PHP_UPLOAD_LIMIT}M/" \
            -e "s/^;\? *post_max_size *=.*/post_max_size = ${PHP_UPLOAD_LIMIT}M/" \
            "$ini"
    done
fi

# Fix Apache request header limits
# (defaults live in /etc/apache2/conf.d/limits.conf; override here)
if [ -v "APACHE_LIMIT_REQUEST_FIELD_SIZE" ]
then
    echo "Changing LimitRequestFieldSize to ${APACHE_LIMIT_REQUEST_FIELD_SIZE}"
    sed -i "s/^LimitRequestFieldSize.*/LimitRequestFieldSize ${APACHE_LIMIT_REQUEST_FIELD_SIZE}/" /etc/apache2/conf.d/limits.conf
fi

if [ -v "APACHE_LIMIT_REQUEST_LINE" ]
then
    echo "Changing LimitRequestLine to ${APACHE_LIMIT_REQUEST_LINE}"
    sed -i "s/^LimitRequestLine.*/LimitRequestLine ${APACHE_LIMIT_REQUEST_LINE}/" /etc/apache2/conf.d/limits.conf
fi

if [ -v "APACHE_LIMIT_REQUEST_FIELDS" ]
then
    echo "Changing LimitRequestFields to ${APACHE_LIMIT_REQUEST_FIELDS}"
    sed -i "s/^LimitRequestFields.*/LimitRequestFields ${APACHE_LIMIT_REQUEST_FIELDS}/" /etc/apache2/conf.d/limits.conf
fi

# Apache listening port. Rootless container runtimes can't bind
# privileged ports (<1024) even inside the container, so expose the
# port as an env var. Default preserves the historical 80
# The Alpine image doesn't ship an in-container SSL config, so only HTTP is
# handled.
# Users running SSL on this image use their own vhost and can bind
# whatever port they want there.
if [ -v "APACHE_HTTP_PORT" ] && [ "$APACHE_HTTP_PORT" != "80" ]
then
    echo "Changing Apache HTTP port to ${APACHE_HTTP_PORT}"
    sed -i -E "s/^Listen\s+80\b/Listen ${APACHE_HTTP_PORT}/" /etc/apache2/httpd.conf
    sed -i -E "s/<VirtualHost\s+\*:80>/<VirtualHost *:${APACHE_HTTP_PORT}>/" /etc/apache2/conf.d/default.conf
fi

# If the Oauth DB files are not present copy the vendor files over to the db migrations
if [ ! -f "/var/www/html/database/migrations/*create_oauth*" ]
then
  cp -a /var/www/html/vendor/laravel/passport/database/migrations/* /var/www/html/database/migrations/
fi

if [ "${SESSION_DRIVER}" == "database" ]
then
  cp -a /var/www/html/vendor/laravel/framework/src/Illuminate/Session/Console/stubs/database.stub /var/www/html/database/migrations/2021_05_06_0000_create_sessions_table.php
fi

php artisan migrate --force
php artisan config:clear
php artisan config:cache

touch /var/www/html/storage/logs/laravel.log
chown -R apache:root /var/www/html/storage/logs/laravel.log

export APACHE_LOG_DIR=/var/log/apache2
exec httpd -DNO_DETACH < /dev/null
