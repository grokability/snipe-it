#!/bin/bash

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
  /var/www/html/artisan key:generate --show
  exit
fi

if [ -f /var/lib/snipeit/ssl/snipeit-ssl.crt -a -f /var/lib/snipeit/ssl/snipeit-ssl.key ]
then
  a2enmod ssl
else
  a2dismod ssl
fi

# create data directories
# Note: Keep in sync with expected directories by the app
# https://github.com/grokability/snipe-it/blob/master/app/Console/Commands/RestoreFromBackup.php#L232
for dir in \
  'data/private_uploads' \
  'data/private_uploads/assets' \
  'data/private_uploads/accessories' \
  'data/private_uploads/audits' \
  'data/private_uploads/components' \
  'data/private_uploads/consumables' \
  'data/private_uploads/eula-pdfs' \
  'data/private_uploads/imports' \
  'data/private_uploads/models' \
  'data/private_uploads/users' \
  'data/private_uploads/licenses' \
  'data/private_uploads/signatures' \
  'data/uploads/accessories' \
  'data/uploads/assets' \
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

chown -R docker:root /var/lib/snipeit/data/*
chown -R docker:root /var/lib/snipeit/dumps
chown -R docker:root /var/lib/snipeit/keys
chown -R docker:root /var/www/html/storage/framework/cache

# Fix php settings
if [ -v "PHP_UPLOAD_LIMIT" ]
then
    find /etc/php -type f -name php.ini | while IFS= read -r ini; do
        echo "Changing upload limit to ${PHP_UPLOAD_LIMIT}M in $ini"
        sed -i \
            -e "s/^;\? *upload_max_filesize *=.*/upload_max_filesize = ${PHP_UPLOAD_LIMIT}M/" \
            -e "s/^;\? *post_max_size *=.*/post_max_size = ${PHP_UPLOAD_LIMIT}M/" \
            "$ini"
    done
fi

# Fix Apache request header limits
# (defaults live in /etc/apache2/conf-available/limits.conf; override here)
if [ -v "APACHE_LIMIT_REQUEST_FIELD_SIZE" ]
then
    echo "Changing LimitRequestFieldSize to ${APACHE_LIMIT_REQUEST_FIELD_SIZE}"
    sed -i "s/^LimitRequestFieldSize.*/LimitRequestFieldSize ${APACHE_LIMIT_REQUEST_FIELD_SIZE}/" /etc/apache2/conf-available/limits.conf
fi

if [ -v "APACHE_LIMIT_REQUEST_LINE" ]
then
    echo "Changing LimitRequestLine to ${APACHE_LIMIT_REQUEST_LINE}"
    sed -i "s/^LimitRequestLine.*/LimitRequestLine ${APACHE_LIMIT_REQUEST_LINE}/" /etc/apache2/conf-available/limits.conf
fi

if [ -v "APACHE_LIMIT_REQUEST_FIELDS" ]
then
    echo "Changing LimitRequestFields to ${APACHE_LIMIT_REQUEST_FIELDS}"
    sed -i "s/^LimitRequestFields.*/LimitRequestFields ${APACHE_LIMIT_REQUEST_FIELDS}/" /etc/apache2/conf-available/limits.conf
fi

# Apache listening ports. Rootless container runtimes can't bind
# privileged ports (<1024) even inside the container, so expose both
# ports as env vars. Defaults preserve the historical 80/443
# Port substitution happens in both ports.conf (Listen directive)
# and the VirtualHost conf so Apache's module-side bind and the vhost match.
# sed with --follow-symlinks because a2ensite symlinks 001-default-ssl.conf
# from sites-available into sites-enabled.
# Trusted forwarded-header promotion for REMOTE_LOGIN. Snipe-IT rejects
# HTTP_-prefixed server variable names in login_remote_user_header_name
# (commit a5927ca21e, hr9m-63rg) because PHP populates $_SERVER[HTTP_*]
# directly from inbound request headers, which lets any client spoof the
# auth header and get logged in as any active user. Setups that
# terminate SSO at an upstream proxy (oauth2-proxy, Traefik ForwardAuth,
# Keycloak gatekeeper) have a legitimate trusted header on the inbound
# request, but the admin can't make Snipe-IT read it directly without
# reopening that attack surface. The right place to promote the trusted
# header to a non-HTTP_* server variable is Apache, so Snipe-IT reads
# REMOTE_USER (the default) and the defensive guard stays intact.
#
# Set REMOTE_LOGIN_FORWARDED_HEADER to the inbound header name your
# upstream sets (e.g. X-Forwarded-Email, X-Forwarded-User). Only do this
# when the upstream STRIPS any client-supplied value of that header
# BEFORE setting its own. Otherwise this reopens the exact pre-auth
# login spoofing surface the guard closed. Issue #19942.
if [ -v "REMOTE_LOGIN_FORWARDED_HEADER" ] && [ -n "$REMOTE_LOGIN_FORWARDED_HEADER" ]
then
    echo "Promoting trusted inbound header '${REMOTE_LOGIN_FORWARDED_HEADER}' to REMOTE_USER for REMOTE_LOGIN"
    cat > /etc/apache2/conf-available/remote-login-forwarded.conf <<EOF
# Generated by startup.sh from REMOTE_LOGIN_FORWARDED_HEADER="${REMOTE_LOGIN_FORWARDED_HEADER}".
# Promotes the inbound ${REMOTE_LOGIN_FORWARDED_HEADER} header to the
# REMOTE_USER server variable so Snipe-IT's REMOTE_LOGIN sees a
# non-HTTP_* value and its defensive guard accepts it. Set your
# Snipe-IT REMOTE_LOGIN header-name back to REMOTE_USER (the default)
# after enabling this.
SetEnvIfNoCase ${REMOTE_LOGIN_FORWARDED_HEADER} "^(.+)\$" REMOTE_USER=\$1
EOF
    a2enconf remote-login-forwarded
else
    # Admin un-set the var on a container restart. Drop the previous
    # generated conf so we don't keep promoting a header the admin no
    # longer wants trusted.
    rm -f /etc/apache2/conf-available/remote-login-forwarded.conf
    a2disconf remote-login-forwarded 2>/dev/null || true
fi

if [ -v "APACHE_HTTP_PORT" ] && [ "$APACHE_HTTP_PORT" != "80" ]
then
    echo "Changing Apache HTTP port to ${APACHE_HTTP_PORT}"
    sed -i --follow-symlinks -E "s/^Listen\s+80\b/Listen ${APACHE_HTTP_PORT}/" /etc/apache2/ports.conf
    sed -i --follow-symlinks -E "s/<VirtualHost\s+\*:80>/<VirtualHost *:${APACHE_HTTP_PORT}>/" /etc/apache2/sites-enabled/000-default.conf
fi

if [ -v "APACHE_HTTPS_PORT" ] && [ "$APACHE_HTTPS_PORT" != "443" ]
then
    echo "Changing Apache HTTPS port to ${APACHE_HTTPS_PORT}"
    sed -i --follow-symlinks -E "s/^(\s*)Listen\s+443\b/\1Listen ${APACHE_HTTPS_PORT}/" /etc/apache2/ports.conf
    sed -i --follow-symlinks -E "s/<VirtualHost\s+_default_:443>/<VirtualHost _default_:${APACHE_HTTPS_PORT}>/" /etc/apache2/sites-available/001-default-ssl.conf
fi

# If the Oauth DB files are not present copy the vendor files over to the db migrations
if [ ! -f "/var/www/html/database/migrations/*create_oauth*" ]
then
  cp -ax /var/www/html/vendor/laravel/passport/database/migrations/* /var/www/html/database/migrations/
fi

if [ "$SESSION_DRIVER" = "database" ]
then
  cp -ax /var/www/html/vendor/laravel/framework/src/Illuminate/Session/Console/stubs/database.stub /var/www/html/database/migrations/2021_05_06_0000_create_sessions_table.php
fi

php artisan migrate --force
php artisan config:clear
php artisan config:cache
php artisan view:clear

# we do this after the artisan commands to ensure that if the laravel
# log got created by root, we set the permissions back
touch /var/www/html/storage/logs/laravel.log
chown -R docker:root /var/www/html/storage/logs/laravel.log

exec supervisord -c /supervisord.conf
