#!/bin/bash
# Definiši putanju do tvoje Laravel aplikacije
APP_DIR="/var/www/html/snipeit8"  # Zameni sa ispravnim putem ako je potrebno

# Prebaci se u direktorijum aplikacije
cd $APP_DIR || exit

# 1. Oèisti konfiguracijski keš
echo "Brisanje konfiguracijskog keša..."
php artisan config:clear

# 2. Oèisti keš rute
echo "Brisanje keša ruta..."
php artisan route:clear

# 3. Oèisti keš pogleda (views)
echo "Brisanje keša pogleda..."
php artisan view:clear

# 4. Oèisti aplikacijski keš
echo "Brisanje aplikacijskog keša..."
php artisan cache:clear

# 5. Oèisti keš za sesije
echo "Brisanje sesijskog keša..."
php artisan session:clear

# 6. Oèisti compiled fajlove (ako je potrebno)
echo "Brisanje compiled fajlova..."
php artisan clear-compiled

# 7. Oèisti autoload fajlove (ako se promene klase)
echo "Brisanje autoload fajlova..."
sudo -u www-data composer dump-autoload