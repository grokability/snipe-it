#!/bin/bash
#/ Usage: snipeit.sh [-vh]
#/
#/ Install Snipe-IT open source asset management.
#/
#/ OPTIONS:
#/   -v | --verbose    Enable verbose output.
#/   -h | --help       Show this message.

######################################################
#           Snipe-It Install Script                  #
#          Script created by Mike Tucker             #
#            mtucker6784@gmail.com                   #
#                                                    #
# Feel free to modify, but please give               #
# credit where it's due. Thanks!                     #
#                                                    #
#         Updated Snipe-IT Install Script            #
#          Update created by Aaron Myers             #
# Change log                                         #
# * verify support for Ubuntu 24.04 -> 25.04         #
# * add support for linux Mint 21 -> 22 inclusive    #
# * add support for linux mint 22.1                  #
# * add support for php8.2, awslinux2, alma 8/9      #
# * fix rocky8/9 support                             #
# * remove Fedora support because short timelines    #
# * Added support for CentOS/Rocky 9                 #
# * Fixed CentOS 7 repository for PHP 7.4            #
# * Removed support for CentOS 6                     #
# * Removed support for Ubuntu < 20.04               #
# * Removed support for Ubuntu 21 (EOL)              #
# * Removed support for Debian < 9 (EOL)             #
# * Fixed permissions issue with Laravel cache       #
# * Moved OS check to start of script                #
# * Fixed timezone awk                               #
# * Minor display and logging improvements           #
######################################################

# Parse arguments
while true; do
  case "$1" in
    -h|--help)
      show_help=true
      shift
      ;;
    -v|--verbose)
      set -x
      verbose=true
      shift
      ;;
    -*)
      echo "Error: invalid argument: '$1'" 1>&2
      exit 1
      ;;
    *)
      break
      ;;
  esac
done

print_usage () {
  grep '^#/' <"$0" | cut -c 4-
  exit 1
}

if [ -n "$show_help" ]; then
  print_usage
else
  for x in "$@"; do
    if [ "$x" = "--help" ] || [ "$x" = "-h" ]; then
      print_usage
    fi
  done
fi

# ensure running as root
if [ "$(id -u)" != "0" ]; then
    #Debian doesnt have sudo if root has a password.
    if ! hash sudo 2>/dev/null; then
        exec su -c "$0" "$@"
    else
        exec sudo "$0" "$@"
    fi
fi

clear

readonly APP_USER="snipeitapp"
readonly APP_NAME="snipeit"
readonly APP_PATH="/var/www/html/$APP_NAME"
readonly APP_LOG="/var/log/snipeit-install.log"
readonly COMPOSER_PATH="/home/$APP_USER"
is_mint=false

progress () {
  spin[0]="-"
  spin[1]="\\"
  spin[2]="|"
  spin[3]="/"

  echo -n " "
  while kill -0 "$pid" > /dev/null 2>&1; do
    for i in "${spin[@]}"; do
      echo -ne "\\b$i"
      sleep .3
    done
  done
  echo ""
}

log () {
  if [ -n "$verbose" ]; then
    eval "$@" |& tee -a /var/log/snipeit-install.log
  else
    eval "$@" |& tee -a /var/log/snipeit-install.log >/dev/null 2>&1
  fi
}

eol () {
  if [[ "$distro" == "Ubuntu" ]] || [[ "$distro" == "Debian" ]] || [[ "$distro" == "Raspbian" ]] ; then
    echo -e "\e[31m** \n $distro version $version ($codename) has reached end of life (EOL) and is not supported\n**\e[0m"
  else
    echo "$distro version $version has reached end of life (EOL) and is not supported"
  fi
}

install_packages () {
  case $distro in
    Ubuntu|Debian)
      for p in $PACKAGES; do
        if dpkg -s "$p" >/dev/null 2>&1; then
          echo "  * $p already installed"
        else
          echo "  * Installing $p"
          log "DEBIAN_FRONTEND=noninteractive apt-get install -y $p"
        fi
      done;
      ;;
    Raspbian)
      for p in $PACKAGES; do
        if dpkg -s "$p" >/dev/null 2>&1; then
          echo "  * $p already installed"
        else
          echo "  * Installing $p"
          log "DEBIAN_FRONTEND=noninteractive apt-get install -y -t buster $p"
        fi
      done;
      ;;
    Centos)
      for p in $PACKAGES; do
        if yum list installed "$p" >/dev/null 2>&1; then
          echo "  * $p already installed"
        else
          echo "  * Installing $p"
          log "yum -y install $p"
        fi
      done;
      ;;
  esac
}

create_virtualhost () {
  {
    echo "<VirtualHost *:80>"
    echo "  <Directory $APP_PATH/public>"
    echo "      Allow From All"
    echo "      AllowOverride All"
    echo "      Options -Indexes"
    echo "  </Directory>"
    echo ""
    echo "  DocumentRoot $APP_PATH/public"
    echo "  ServerName $fqdn"
    echo "</VirtualHost>"
  } >> "$apachefile"
}

create_user () {
  echo "* Creating Snipe-IT user."

  if [[ "$distro" == "Ubuntu" ]] || [[ "$distro" == "Debian" ]] || [[ "$distro" == "Raspbian" ]] ; then
    /usr/sbin/adduser --quiet --disabled-password --gecos 'Snipe-IT User' "$APP_USER"
  else
    adduser -c "Snipe-IT User" "$APP_USER"
  fi

  # Add the user to the apache group so the app can write to any files apache
  # creates (eg, if apache process creates the log, but then a an app-user-owned
  # cron also tries writing
  usermod -a -G "$apache_group" "$APP_USER"

  # Now do the reverse -- so apache can write to the log that the user may
  # have created. This was actively a problem on new installs, hobbling
  # imports
  # redefining these variables just for clarity
  apache_user="$apache_group"
  app_group="$APP_USER"
  usermod -a -G "$app_group" "$apache_user"
}

run_as_app_user () {
  if ! hash sudo 2>/dev/null; then
      su - $APP_USER -c "$@"
  else
      sudo -i -u $APP_USER "$@"
  fi
}

install_composer () {
  # https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
  EXPECTED_SIGNATURE="$(wget -q -O - https://composer.github.io/installer.sig)"

  if [[ "$distro" == "Debian" ]]; then
    wget -q -O $COMPOSER_PATH/composer-setup.php https://getcomposer.org/installer && chown $APP_USER:$APP_USER $COMPOSER_PATH/composer-setup.php
    ACTUAL_SIGNATURE="$(sha384sum $COMPOSER_PATH/composer-setup.php | awk '{ print $1 }')"
  else
    run_as_app_user php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_SIGNATURE="$(run_as_app_user php -r "echo hash_file('SHA384', 'composer-setup.php');")"
  fi

  if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
    >&2 echo 'ERROR: Invalid composer installer signature'
    exit 1
  fi

  if [[ "$distro" == "Debian" ]]; then
    run_as_app_user php $COMPOSER_PATH/composer-setup.php
    run_as_app_user rm $COMPOSER_PATH/composer-setup.php
  else
    run_as_app_user php composer-setup.php
    run_as_app_user rm composer-setup.php
  fi

  mv "$(eval echo ~$APP_USER)"/composer.phar /usr/local/bin/composer
}

install_snipeit () {
  create_user
  echo "* Creating MariaDB Database/User."
  mysql -u root --execute="CREATE DATABASE snipeit;CREATE USER snipeit_dbuser@localhost IDENTIFIED BY '$mysqluserpw'; GRANT ALL PRIVILEGES ON snipeit.* TO snipeit_dbuser@localhost;"

  echo -e "\n\n* Cloning Snipe-IT from github to the web directory."
  log "git clone https://github.com/grokability/snipe-it $APP_PATH" & pid=$!
  progress
  pushd $APP_PATH
  git checkout master
  popd

  echo "* Configuring .env file."
  cp "$APP_PATH/.env.example" "$APP_PATH/.env"

  #TODO escape SED delimiter in variables
  sed -i '1 i\#Created By Snipe-it Installer' "$APP_PATH/.env"
  sed -i "s|^\\(APP_TIMEZONE=\\).*|\\1$tzone|" "$APP_PATH/.env"
  sed -i "s|^\\(DB_HOST=\\).*|\\1localhost|" "$APP_PATH/.env"
  sed -i "s|^\\(DB_DATABASE=\\).*|\\1snipeit|" "$APP_PATH/.env"
  sed -i "s|^\\(DB_USERNAME=\\).*|\\1snipeit_dbuser|" "$APP_PATH/.env"
  sed -i "s|^\\(DB_PASSWORD=\\).*|\\1'$mysqluserpw'|" "$APP_PATH/.env"
  sed -i "s|^\\(APP_URL=\\).*|\\1http://$fqdn|" "$APP_PATH/.env"

  echo "* Installing composer."
  install_composer

  echo "* Setting permissions."
  for chmod_dir in "$APP_PATH/storage" "$APP_PATH/public/uploads" "$APP_PATH/bootstrap/cache"; do
    chmod -R 775 "$chmod_dir"
  done

  chown -R "$APP_USER":"$apache_group" "$APP_PATH"

  echo "* Running composer."
  # We specify the path to composer because CentOS lacks /usr/local/bin in $PATH when using sudo
  if [[ "$distro" == "Debian" ]]; then
    run_as_app_user /usr/local/bin/composer install --no-dev --prefer-source --working-dir "$APP_PATH"
  else
    echo "* This can take 5 minutes or more. Tail $APP_LOG for more full command output." & pid=$!
    progress
    log "run_as_app_user /usr/local/bin/composer install --no-dev --prefer-source --working-dir "$APP_PATH""
  fi

  chgrp -R "$apache_group" "$APP_PATH/vendor"

  echo "* Generating the application key."
  log "php $APP_PATH/artisan key:generate --force"

  echo "* Artisan Migrate."
  log "php $APP_PATH/artisan migrate --force"

  echo "* Creating scheduler cron."
  (echo "* * * * * /usr/bin/php $APP_PATH/artisan schedule:run >> /dev/null 2>&1") | run_as_app_user crontab -
}

set_firewall () {
  if [ "$(firewall-cmd --state)" == "running" ]; then
    echo "* Configuring firewall to allow HTTP traffic only."
    log "firewall-cmd --zone=public --add-port=http/tcp --permanent"
    log "firewall-cmd --reload"
  fi
}

set_selinux () {
  #Check if SELinux is enforcing
  if [ "$(getenforce)" == "Enforcing" ]; then
    echo "* Configuring SELinux."
    #Required for ldap integration
    setsebool -P httpd_can_connect_ldap on
    #Sets SELinux context type so that scripts running in the web server process are allowed read/write access
    chcon -R -h -t httpd_sys_rw_content_t "$APP_PATH/storage/"
    chcon -R -h -t httpd_sys_rw_content_t "$APP_PATH/public/"
  fi
}

set_hosts () {
  echo "* Setting up hosts file."
  echo >> /etc/hosts "127.0.0.1 $(hostname) $fqdn"
}

rename_default_vhost () {
    log "mv /etc/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled/111-default.conf"
    log "mv /etc/apache2/sites-enabled/snipeit.conf /etc/apache2/sites-enabled/000-snipeit.conf"
}


if [[ -f /etc/debian_version || -f /etc/lsb-release ]]; then
  distro="$(lsb_release -is)"
  version="$(lsb_release -rs)"
  codename="$(lsb_release -cs)"
elif [ -f /etc/os-release ]; then
  # shellcheck disable=SC1091
  distro="$(source /etc/os-release && echo "$ID")"
  # shellcheck disable=SC1091
  version="$(source /etc/os-release && echo "$VERSION_ID")"
  #Order is important here.  If /etc/os-release and /etc/centos-release exist, we're on centos 7.
  #If only /etc/centos-release exist, we're on centos6(or earlier).  Centos-release is less parsable,
  #so lets assume that it's version 6 (Plus, who would be doing a new install of anything on centos5 at this point..)
  #/etc/os-release properly detects fedora
elif [ -f /etc/centos-release ]; then
  distro="centos"
  version="6"
else
  distro="unsupported"
fi

echo '
       _____       _                  __________
      / ___/____  (_)___  ___        /  _/_  __/
      \__ \/ __ \/ / __ \/ _ \______ / /  / /
     ___/ / / / / / /_/ /  __/_____// /  / /
    /____/_/ /_/_/ .___/\___/     /___/ /_/
                /_/
'

echo ""
echo "  Welcome to Snipe-IT Inventory Installer for CentOS, Rocky, Debian, and Ubuntu!"
echo ""
echo "  Installation log located: $APP_LOG"
echo ""
shopt -s nocasematch
case $distro in
  *ubuntu*)
    echo "  The installer has detected $distro version $version codename $codename."
    distro=Ubuntu
    apache_group=www-data
    apachefile=/etc/apache2/sites-available/$APP_NAME.conf
    ;;
  *linuxmint*)
    echo "  The installer has detected $distro version $version codename $codename."
    distro=Ubuntu
    is_mint=true
    apache_group=www-data
    apachefile=/etc/apache2/sites-available/$APP_NAME.conf
    ;;
  *raspbian*)
    echo "  The installer has detected $distro version $version codename $codename."
    distro=Raspbian
    apache_group=www-data
    apachefile=/etc/apache2/sites-available/$APP_NAME.conf
    ;;
  *Debian|debian*)
    echo "  The installer has detected $distro version $version codename $codename."
    distro=Debian
    apache_group=www-data
    apachefile=/etc/apache2/sites-available/$APP_NAME.conf
    ;;
  *amzn*|*redhat*|*alma*|*rhel*|*rocky*|*centos*)
    echo "  The installer has detected $distro version $version."
    distro=Centos
    apache_group=apache
    apachefile=/etc/httpd/conf.d/$APP_NAME.conf
    ;;
  *fedora*)
    echo "  The installer does not support Fedora"
    exit 1
    ;;
  *)
    echo "   The installer was unable to determine your OS. Exiting for safety. Exiting for safety."
    exit 1
    ;;
esac
shopt -u nocasematch

set_fqdn () {
   echo -n "  Q. What is the FQDN of your server? ($(hostname --fqdn)): "
   read -r fqdn
   if [ -z "$fqdn" ]; then
     readonly fqdn="$(hostname --fqdn)"
   fi
   echo "     Setting to $fqdn"
   echo ""
}

set_dbpass () {
   ans=default
   until [[ $ans == "yes" ]] || [[ $ans == "no" ]]; do
      echo -n "  Q. Do you want to automatically create the SnipeIT database user password? (y/n) "
      read -r setpw

      case $setpw in
         [yY] | [yY][Ee][Ss] )
         mysqluserpw="$(< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c16; echo)"
         echo ""
         ans="yes"
      ;;
      [nN] | [n|N][O|o] )
         echo -n  "  Q. What do you want your snipeit user password to be?"
         read -rs mysqluserpw
         echo ""
         ans="no"
      ;;
      *)  echo "  Invalid answer. Please type y or n"
      ;;
      esac
   done
}

case $distro in
  Debian)
    if [[ "$version" =~ ^12 ]]; then
        # Install for Debian 12.x
        set_fqdn
        set_dbpass
        tzone=$(cat /etc/timezone)

        echo "* Adding PHP repository."
        log "apt-get install -y apt-transport-https lsb-release ca-certificates"
        log "wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg"
        echo "deb https://packages.sury.org/php/ $codename main" > /etc/apt/sources.list.d/php.list

        echo -n "* Updating installed packages."
        log "apt-get update && apt-get -y upgrade" & pid=$!
        progress

        echo "* Installing Apache httpd, PHP, MariaDB and other requirements."
        PACKAGES="mariadb-server mariadb-client apache2 libapache2-mod-php8.2 php8.2  php8.2-curl php8.2-mysql php8.2-gd php8.2-ldap php8.2-zip php8.2-mbstring php8.2-xml php8.2-bcmath curl git unzip"
        install_packages

        echo "* Configuring Apache."
        create_virtualhost
        /usr/sbin/a2enmod rewrite
        /usr/sbin/a2ensite $APP_NAME.conf
        rename_default_vhost

        set_hosts

        install_snipeit

        echo "* Restarting Apache httpd."
        /usr/sbin/service apache2 restart

        echo "* Clearing cache and setting final permissions."
        chmod 777 -R $APP_PATH/storage/framework/cache/
        run_as_app_user php $APP_PATH/artisan cache:clear
        chmod 775 -R $APP_PATH/storage/

    elif [[ "$version" =~ ^11 ]]; then
        # Install for Debian 11.x
        set_fqdn
        set_dbpass
        tzone=$(cat /etc/timezone)

        echo "* Adding PHP repository."
        log "apt-get install -y apt-transport-https lsb-release ca-certificates"
        log "wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg"
        echo "deb https://packages.sury.org/php/ $codename main" > /etc/apt/sources.list.d/php.list

        echo -n "* Updating installed packages."
        log "apt-get update && apt-get -y upgrade" & pid=$!
        progress

        echo "* Installing Apache httpd, PHP, MariaDB and other requirements."
        PACKAGES="mariadb-server mariadb-client apache2 libapache2-mod-php8.2 php8.2  php8.2-curl php8.2-mysql php8.2-gd php8.2-ldap php8.2-zip php8.2-mbstring php8.2-xml php8.2-bcmath curl git unzip"
        install_packages

        echo "* Configuring Apache."
        create_virtualhost
        /usr/sbin/a2enmod rewrite
        /usr/sbin/a2ensite $APP_NAME.conf
        rename_default_vhost

        set_hosts

        install_snipeit

        echo "* Restarting Apache httpd."
        /usr/sbin/service apache2 restart

        echo "* Clearing cache and setting final permissions."
        chmod 777 -R $APP_PATH/storage/framework/cache/
        run_as_app_user php $APP_PATH/artisan cache:clear
        chmod 775 -R $APP_PATH/storage/

    elif [[ "$version" =~ ^10 ]]; then
        # Install for Debian 10.x
        set_fqdn
        set_dbpass
        tzone=$(cat /etc/timezone)

        echo "* Adding PHP repository."
        log "apt-get install -y apt-transport-https lsb-release ca-certificates"
        log "wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg"
        echo "deb https://packages.sury.org/php/ $codename main" > /etc/apt/sources.list.d/php.list

        echo -n "* Updating installed packages."
        log "apt-get update && apt-get -y upgrade" & pid=$!
        progress

        echo "* Installing Apache httpd, PHP, MariaDB and other requirements."
        PACKAGES="mariadb-server mariadb-client apache2 libapache2-mod-php8.2 php8.2  php8.2-curl php8.2-mysql php8.2-gd php8.2-ldap php8.2-zip php8.2-mbstring php8.2-xml php8.2-bcmath curl git unzip"
        install_packages

        echo "* Configuring Apache."
        create_virtualhost
        /usr/sbin/a2enmod rewrite
        /usr/sbin/a2ensite $APP_NAME.conf
        rename_default_vhost

        set_hosts

        install_snipeit

        echo "* Restarting Apache httpd."
        /usr/sbin/service apache2 restart

        echo "* Clearing cache and setting final permissions."
        chmod 777 -R $APP_PATH/storage/framework/cache/
        run_as_app_user php $APP_PATH/artisan cache:clear
        chmod 775 -R $APP_PATH/storage/

    elif [[ "$version" =~ ^9 ]]; then
        eol
        exit 1
    else
        echo "Unsupported Debian version. Version found: $version"
        exit 1
    fi
  ;;
  Ubuntu)
    if [ "${version//./}" -ge "2304" ]; then
        # Install for Ubuntu 23.04 and above
        set_fqdn
        set_dbpass
        tzone=$(cat /etc/timezone)

        echo -n "* Updating installed packages."
        log "apt-get update && DEBIAN_FRONTEND=noninteractive apt-get -y upgrade" & pid=$!
        progress

        echo "* Installing Apache httpd, PHP, MariaDB and other requirements."
        PACKAGES="cron mariadb-server mariadb-client apache2 libapache2-mod-php php php-curl php-mysql php-gd php-ldap php-zip php-mbstring php-xml php-bcmath curl git unzip wget"
        install_packages

        echo "* Configuring Apache."
        create_virtualhost
        log "phpenmod mcrypt"
        log "phpenmod mbstring"
        log "a2enmod rewrite"
        log "a2ensite $APP_NAME.conf"
        rename_default_vhost

        set_hosts

        echo "* Starting MariaDB."
        log "systemctl start mariadb.service"

        install_snipeit

        echo "* Restarting Apache httpd."
        log "systemctl restart apache2"

        echo "* Clearing cache and setting final permissions."
        chmod 777 -R $APP_PATH/storage/framework/cache/
        log "run_as_app_user php $APP_PATH/artisan cache:clear"
        chmod 775 -R $APP_PATH/storage/
    elif [[ "${is_mint}" == "true" ]]; then
        # Install for Linux Mint 21.2 - 22.1

        if [[ "${version//.*/}" -eq "22" ]] ; then
            ubuntu_codename=noble
        elif [[ "${version//.*/}" -eq "21" ]]; then
            ubuntu_codename=jammy
        else
            echo "Unsupported Linux Mint version. Version found: $version"
            exit 1
        fi

        set_fqdn
        set_dbpass
        tzone=$(cat /etc/timezone)

        echo "* Set up Ondrej PHP repository"
        echo "# Odrej PHP repo for ability to choose non-distro PHP versions" > /etc/apt/sources.list.d/ppa_ondrej_php_$ubuntu_codename.list
        echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu $ubuntu_codename main" >> /etc/apt/sources.list.d/ppa_ondrej_php_$ubuntu_codename.list
        sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C

        echo -n "* Updating installed packages."
        log "apt-get update && DEBIAN_FRONTEND=noninteractive apt-get -y upgrade" & pid=$!
        progress

        echo "* Installing Apache httpd, PHP, MariaDB and other requirements."
        PACKAGES="cron mariadb-server mariadb-client apache2 libapache2-mod-php8.3 php8.3  php8.3-curl php8.3-mysql php8.3-gd php8.3-ldap php8.3-zip php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-cli curl git unzip"
        install_packages

        echo "* Configuring Apache."
        create_virtualhost
        log "phpenmod mcrypt"
        log "phpenmod mbstring"
        log "a2enmod rewrite"
        log "a2ensite $APP_NAME.conf"
        rename_default_vhost

        set_hosts

        echo "* Starting MariaDB."
        log "systemctl start mariadb.service"

        install_snipeit

        echo "* Restarting Apache httpd."
        log "systemctl restart apache2"

        echo "* Clearing cache and setting final permissions."
        chmod 777 -R $APP_PATH/storage/framework/cache/
        log "run_as_app_user php $APP_PATH/artisan cache:clear"
        chmod 775 -R $APP_PATH/storage/
    elif [ "${version//./}" -eq "2204" ]; then
        # Install for Ubuntu 22.04
        set_fqdn
        set_dbpass
        tzone=$(cat /etc/timezone)

        echo "* Set up Ondrej PHP repository"
        echo "# Odrej PHP repo for ability to choose non-distro PHP versions" > /etc/apt/sources.list.d/ppa_ondrej_php_$codename.list
        echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu $codename main" >> /etc/apt/sources.list.d/ppa_ondrej_php_$codename.list
        sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C

        echo -n "* Updating installed packages."
        log "apt-get update && DEBIAN_FRONTEND=noninteractive apt-get -y upgrade" & pid=$!
        progress

        echo "* Installing Apache httpd, PHP, MariaDB and other requirements."
        PACKAGES="cron mariadb-server mariadb-client apache2 libapache2-mod-php8.2 php8.2  php8.2-curl php8.2-mysql php8.2-gd php8.2-ldap php8.2-zip php8.2-mbstring php8.2-xml php8.2-bcmath curl git unzip"
        install_packages

        echo "* Configuring Apache."
        create_virtualhost
        log "phpenmod mcrypt"
        log "phpenmod mbstring"
        log "a2enmod rewrite"
        log "a2ensite $APP_NAME.conf"
        rename_default_vhost

        set_hosts

        echo "* Starting MariaDB."
        log "systemctl start mariadb.service"

        install_snipeit

        echo "* Restarting Apache httpd."
        log "systemctl restart apache2"

        echo "* Clearing cache and setting final permissions."
        chmod 777 -R $APP_PATH/storage/framework/cache/
        log "run_as_app_user php $APP_PATH/artisan cache:clear"
        chmod 775 -R $APP_PATH/storage/
    elif [ "${version//./}" == "2110" ]; then
        # Ubuntu 21.10 is no longer supported
        echo "Unsupported Ubuntu version. Version found: $version"
        exit 1
    elif [ "${version//./}" == "2004" ]; then
        # Install for Ubuntu 20.04
        set_fqdn
        set_dbpass
        tzone=$(cat /etc/timezone)

        echo "* Set up Ondrej PHP repository"
        echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu $codename main" >> /etc/apt/sources.list.d/ppa_ondrej_php_$codename.list
        sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C

        echo -n "* Updating installed packages."
        log "apt-get update && DEBIAN_FRONTEND=noninteractive apt-get -y upgrade" & pid=$!
        progress

        echo "* Installing Apache httpd, PHP, MariaDB and other requirements."
        PACKAGES="cron mariadb-server mariadb-client apache2 libapache2-mod-php8.28.2 php8.2  php8.2-curl php8.2-mysql php8.2-gd php8.2-ldap php8.2-zip php8.2-mbstring php8.2-xml php8.2-bcmath curl git unzip"
        install_packages

        echo "* Configuring Apache."
        create_virtualhost
        log "phpenmod mcrypt"
        log "phpenmod mbstring"
        log "a2enmod rewrite"
        log "a2ensite $APP_NAME.conf"
        rename_default_vhost

        set_hosts

        echo "* Starting MariaDB."
        log "systemctl start mariadb.service"

        install_snipeit

        echo "* Restarting Apache httpd."
        log "systemctl restart apache2"

        echo "* Clearing cache and setting final permissions."
        chmod 777 -R $APP_PATH/storage/framework/cache/
        log "run_as_app_user php $APP_PATH/artisan cache:clear"
        chmod 775 -R $APP_PATH/storage/
    elif [ "${version//./}" == "1804" ]; then
        eol
        exit 1
    else
        echo "Unsupported Ubuntu version. Version found: $version"
        exit 1
    fi
    ;;
  Raspbian)
    if [[ "$version" =~ ^10 ]]; then
        # Install for Raspbian 9.x
        set_fqdn
        set_dbpass
        tzone=$(cat /etc/timezone)
        cat >/etc/apt/sources.list.d/10-buster.list <<EOL
deb http://mirrordirector.raspbian.org/raspbian/ buster main contrib non-free rpi
EOL

    cat >/etc/apt/preferences.d/10-buster <<EOL
Package: *
Pin: release n=stretch
Pin-Priority: 900

Package: *
Pin: release n=buster
Pin-Priority: 750
EOL
        echo "* Set up Ondrej PHP repository"
        echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu $codename main" >> /etc/apt/sources.list.d/ppa_ondrej_php_$codename.list

        echo -n "* Updating installed packages."
        log "apt-get update && DEBIAN_FRONTEND=noninteractive apt-get -y upgrade" & pid=$!
        progress

        echo "* Installing Apache httpd, PHP, MariaDB and other requirements."
        PACKAGES="mariadb-server mariadb-client apache2 libapache2-mod-php8.2 php8.2  php8.2-curl php8.2-mysql php8.2-gd php8.2-ldap php8.2-zip php8.2-mbstring php8.2-xml php8.2-bcmath curl git unzip"
        install_packages

        echo "* Configuring Apache."
        create_virtualhost
        log "phpenmod mbstring"
        log "a2enmod rewrite"
        log "a2ensite $APP_NAME.conf"

        set_hosts

        echo "* Starting MariaDB."
        log "systemctl start mariadb.service"

        echo "* Securing MariaDB."
        /usr/bin/mysql_secure_installation

        install_snipeit

        echo "* Restarting Apache httpd."
        log "systemctl restart apache2"
    else
        echo "Unsupported Raspbian version. Version found: $version"
        exit 1
    fi
  ;;
  Centos)
    if [[ "$version" =~ ^6 ]]; then
        eol
        exit 1
    elif [[ "$version" =~ ^2 || "$distro" == "amzn" ]]; then
        # Install for amazon linux 2
        set_fqdn
        set_dbpass
        tzone=$(timedatectl | gawk -F'[: ]' ' $9 ~ /zone/ {print $11}');

        amazon-linux-extras install -y php8.2

        echo "* Installing Apache httpd, PHP, MariaDB and other requirements."
        PACKAGES="httpd mariadb-server git unzip php php-mysqlnd php-bcmath php-embedded php-gd php-mbstring php-ldap php-json php-simplexml php-process php-zip  php-sodium"
        install_packages

        echo "* Configuring Apache."
        create_virtualhost

        set_hosts

        echo "* Setting MariaDB to start on boot and starting MariaDB."
        log "systemctl enable mariadb.service"
        log "systemctl start mariadb.service"

        install_snipeit

        set_firewall

        echo "* Setting Apache httpd to start on boot and starting service."
        log "systemctl enable httpd.service"
        log "systemctl restart httpd.service"

        echo "* Clearing cache and setting final permissions."
        chmod 777 -R $APP_PATH/storage/framework/cache/
        log "run_as_app_user php $APP_PATH/artisan cache:clear"
        chmod 775 -R $APP_PATH/storage/

        set_selinux

    elif [[ "$version" =~ ^7 ]]; then
        # Install for CentOS/Redhat 7
        set_fqdn
        set_dbpass
        tzone=$(timedatectl | gawk -F'[: ]' ' $9 ~ /zone/ {print $11}');

        echo "* Adding Remi and EPEL-Release repositories."
        log "yum -y install wget epel-release yum-utils" & pid=$!
        progress
        log "yum -y install http://rpms.remirepo.net/enterprise/remi-release-7.rpm" & pid=$!
        progress
        log "yum-config-manager --enable remi-php82"

        echo "* Installing Apache httpd, PHP, MariaDB and other requirements."
        PACKAGES="httpd mariadb-server git unzip php php-mysqlnd php-bcmath php-embedded php-gd php-mbstring php-ldap php-json php-simplexml php-process php-zip"
        install_packages

        echo "* Configuring Apache."
        create_virtualhost

        set_hosts

        echo "* Setting MariaDB to start on boot and starting MariaDB."
        log "systemctl enable mariadb.service"
        log "systemctl start mariadb.service"

        install_snipeit

        set_firewall

        echo "* Setting Apache httpd to start on boot and starting service."
        log "systemctl enable httpd.service"
        log "systemctl restart httpd.service"

        echo "* Clearing cache and setting final permissions."
        chmod 777 -R $APP_PATH/storage/framework/cache/
        log "run_as_app_user php $APP_PATH/artisan cache:clear"
        chmod 775 -R $APP_PATH/storage/

        set_selinux

    elif [[ "$version" =~ ^8 ]]; then
        # Install for CentOS/Redhat 8
        set_fqdn
        set_dbpass
        tzone=$(timedatectl | grep "Time zone" | awk 'BEGIN { FS"("}; {print $3}');

        echo "* Adding Remi and EPEL-Release repositories."
        log "yum -y install wget epel-release yum-utils" & pid=$!
        progress
        log "yum -y install https://rpms.remirepo.net/enterprise/remi-release-8.rpm" & pid=$!
        progress
        log "rpm --import /etc/pki/rpm-gpg/RPM-GPG-KEY-remi.el8"
        log "dnf -y module enable php:remi-8.2" & pid=$!
        progress

        echo "* Installing Apache httpd, PHP, MariaDB and other requirements."
        PACKAGES="httpd mariadb-server git unzip php php-mysqlnd php-bcmath php-embedded php-gd php-mbstring php-ldap php-json php-simplexml php-process php-zip"
        install_packages

        echo "* Configuring Apache."
        create_virtualhost

        set_hosts

        echo "* Setting MariaDB to start on boot and starting MariaDB."
        log "systemctl enable mariadb.service"
        log "systemctl start mariadb.service"

        install_snipeit

        set_firewall

        echo "* Setting Apache httpd to start on boot and starting service."
        log "systemctl enable httpd.service"
        log "systemctl restart httpd.service"

        echo "* Clearing cache and setting final permissions."
        chmod 777 -R $APP_PATH/storage/framework/cache/
        log "run_as_app_user php $APP_PATH/artisan cache:clear"
        chmod 775 -R $APP_PATH/storage/

        set_selinux

    elif [[ "$version" =~ ^9 ]]; then
        # Install for CentOS/Alma/Redhat 9
        set_fqdn
        set_dbpass
        tzone=$(timedatectl | grep "Time zone" | awk 'BEGIN { FS"("}; {print $3}');

        echo "* Adding EPEL-release repository."
        log "dnf -y install wget epel-release" & pid=$!
        progress
        log "yum -y install https://rpms.remirepo.net/enterprise/remi-release-9.rpm" & pid=$!
        progress
        log "rpm --import /etc/pki/rpm-gpg/RPM-GPG-KEY-remi.el9"
        log "dnf -y module enable php:remi-8.2" & pid=$!
        progress

        echo "* Installing Apache httpd, PHP, MariaDB, and other requirements."
        PACKAGES="httpd mariadb-server git unzip php-mysqlnd php-bcmath php-cli php-embedded php-gd php-mbstring php-ldap php-simplexml php-process php-sodium php-pecl-zip php-fpm"
        install_packages

        echo "* Configuring Apache."
        create_virtualhost

        set_hosts

        echo "* Setting MariaDB to start on boot and starting MariaDB."
        log "systemctl enable mariadb.service"
        log "systemctl start mariadb.service"

        install_snipeit

        set_firewall & pid=$!
        progress

        echo "* Setting Apache httpd to start on boot and starting service."
        log "systemctl enable httpd.service"
        log "systemctl restart httpd.service"

        echo "* Setting php-fpm to start on boot and starting service."
        log "systemctl enable php-fpm.service"
        log "systemctl restart php-fpm.service"

        echo "* Clearing cache and setting final permissions."
        chmod 777 -R $APP_PATH/storage/framework/cache/
        log "run_as_app_user php $APP_PATH/artisan cache:clear"
        chmod 775 -R $APP_PATH/storage/

        set_selinux

    else
        echo "Unsupported CentOS version. Version found: $version"
        exit 1
    fi
  ;;
  *)
    echo "Your OS was not detected correctly."
    exit 1
esac

setupmail=default
until [[ $setupmail == "yes" ]] || [[ $setupmail == "no" ]]; do
echo "  Q. Do you want to configure mail server settings now? This can be done later too. "
echo -n "     * You will need mail server address, port, user and password among other items. (y/n) "
read -r setupmail

case $setupmail in
  [yY] | [yY][Ee][Ss] )
    echo -n "  Outgoing mailserver address:"
    read -r mailhost
    sed -i "s|^\\(MAIL_HOST=\\).*|\\1$mailhost|" "$APP_PATH/.env"

    echo -n "  Server port number:"
    read -r mailport
    sed -i "s|^\\(MAIL_PORT=\\).*|\\1$mailport|" "$APP_PATH/.env"

    echo -n "  Username:"
    read -r mailusername
    sed -i "s|^\\(MAIL_USERNAME=\\).*|\\1$mailusername|" "$APP_PATH/.env"

    echo -n "  Password:"
    read -rs mailpassword
    sed -i "s|^\\(MAIL_PASSWORD=\\).*|\\1$mailpassword|" "$APP_PATH/.env"
    echo ""

    echo -n "  Verify TLS certificate on remote server? (true/false):"
    read -r mailverifypeer
    sed -i "s|^\\(MAIL_TLS_VERIFY_PEER=\\).*|\\1$mailverifypeer|" "$APP_PATH/.env"

    echo -n "  From address:"
    read -r mailfromaddr
    sed -i "s|^\\(MAIL_FROM_ADDR=\\).*|\\1$mailfromaddr|" "$APP_PATH/.env"

    echo -n "  From name:"
    read -r mailfromname
    sed -i  "s|^\\(MAIL_FROM_NAME=\\).*|\\1\\'$mailfromname\\'|" "$APP_PATH/.env"

    echo -n "  Reply to address:"
    read -r mailreplytoaddr
    sed -i "s|^\\(MAIL_REPLYTO_ADDR=\\).*|\\1$mailreplytoaddr|" "$APP_PATH/.env"

    echo -n "  Reply to name:"
    read -r mailreplytoname
    sed -i "s|^\\(MAIL_REPLYTO_NAME=\\).*|\\1\\'$mailreplytoname\\'|" "$APP_PATH/.env"
    setupmail="yes"
    ;;
  [nN] | [n|N][O|o] )
    setupmail="no"
    ;;
  *)  echo "  Invalid answer. Please type y or n"
    ;;
esac
done

echo ""
echo "  ***Open http://$fqdn to login to Snipe-IT.***"
echo ""
echo ""
echo "* Installation log located in $APP_LOG."
echo "* Finished!"
sleep 1
