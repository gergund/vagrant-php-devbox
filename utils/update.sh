#!/usr/bin/env bash
echo "UNLink magento"
php build.php --ce-source magento/src/magento --command unlink
echo "UNLink common"
php build.php --ce-source common/src/magento --command unlink

#echo "update Common"
#cd common/
#git checkout develop
#git pull
#cd ..
#
#echo "update Magento branch"
#cd magento/
#git checkout develop
#git pull
#cd ..
#
#echo "update eastpak branch"
#cd eastpak/
#git checkout develop
#git pull
#cd ..
#
#echo "Synchronize translations"
#/bin/cp -Rf common/src/magento/app/locale/* magento/src/magento/app/locale/
#/bin/cp -Rf eastpak/src/magento/app/locale/* magento/src/magento/app/locale/
#
#/bin/cp -Rf common/src/magento/app/design/frontend/vf/default/locale/* magento/src/magento/app/design/frontend/vf/default/locale/
#/bin/cp -Rf eastpak/src/magento/app/design/frontend/vf/eastpak/locale/* magento/src/magento/app/design/frontend/vf/eastpak/locale/

echo "Link"
php build.php --ce-source magento/src/magento --ee-source common/src/magento --command link
php build.php --ce-source magento/src/magento --ee-source wrangler/src/magento --command link

#echo "Change rights to php-fpm:php-fpm"
#chown -R php-fpm:php-fpm common
#chown -R php-fpm:php-fpm magento
#chown -R php-fpm:php-fpm eastpak

echo "Cleaning Cache and FPC"
rm -Rf magento/src/magento/var/cache magento/src/magento/var/full_page_cache
#echo "Cleaning Redis"
#redis-cli -h 127.0.0.1 -p 6381 -n 4 flushdb && redis-cli -h 127.0.0.1 -p 6381 -n 5 flushdb

echo "Restart PHP-FPM and Varnish"
sudo service php-fpm restart
sudo service varnish restart