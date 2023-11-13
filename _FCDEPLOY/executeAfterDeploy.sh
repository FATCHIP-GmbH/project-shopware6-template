#!/usr/bin/env bash


#if [ "prod" = "${environment}" ]; then
#  basepath="/var/www/web535/htdocs/shopware6/${environment}"
#fi
#
#if [ "stage" = "${environment}" ]; then
#  basepath="/var/www/web535/htdocs/shopware6/prod/${environment}"
#fi

basepath="/var/www/XXX/htdocs/${environment}"
PHP_EXE=/usr/local/bin/php8.0/php

FC_FOLDERS=("_FCCONFIG" "_FCPROJECT");
cd ${basepath}
# copy config files depending on environment var
if [ -d "_FCCONFIG/${environment}/files" ]; then
  echo "copying config files for ${environment}"
  cp -R _FCCONFIG/${environment}/files/* "htdocs"
  cp _FCCONFIG/${environment}/files/.env "htdocs"
  cp _FCCONFIG/${environment}/files/.env.docker "htdocs"
  cp _FCCONFIG/${environment}/files/.env.local "htdocs"
  cp _FCCONFIG/${environment}/public/.htaccess "htdocs/public"
fi

echo "removing config folders"
# remove FC_FOLDERS
for f in "${FC_FOLDERS[@]}"
do
  if [ -d "${basepath}/${f[@]}" ]; then
  echo "Removing ${basepath}/${f[@]}"
	\rm -R "${basepath}/${f[@]}"
	fi
done

echo "setting rights"
# make cache folders writeable
chmod -R 775 ${basepath}/htdocs/var > /dev/null 2>&1
chmod 775 ${basepath}/htdocs/bin/console > /dev/null 2>&1
chmod 775 ${basepath}/_FCDEPLOY/*.sh > /dev/null 2>&1

echo "setting up symlinks"
cd ${basepath}
# if [ ! -h "${basepath}/htdocs/files/media" ] ; then
#   echo "${sharedfolders}/files/media symlink missing, recreating"
#     ln -s ../${environment}/shared/shopware/files/media files/media
# fi

# if [ ! -h "${basepath}/htdocs/public/media" ] ; then
#   echo "'public/media' symlink missing, recreating"
#     ln -s "${sharedfolders}/public/media" "${basepath}/htdocs/public/media"
#fi

# if [ ! -h "${basepath}/htdocs/public/sitemap" ] ; then
#  echo "'public/sitemap' symlink missing, recreating"
#  ln -s "${sharedfolders}/public/sitemap" "${basepath}/htdocs/public/sitemap"
# fi

# if [ ! -h "${basepath}/htdocs/public/thumbnail" ] ; then
#   echo "'public/thumbnail' symlink missing, recreating"
#   ln -s "${sharedfolders}/public/thumbnail" "${basepath}/htdocs/public/thumbnail"
# fi

# make restic lib executable
# chmod 755 ${basepath}/fcShopBackup/lib/restic/restic_0.9.0_linux_amd64

echo "clearing caches and warming theme"
if [ -d "${basepath}/htdocs/var/cache" ]; then rm -rf "${basepath}/htdocs/var/cache"/*; fi
if [ -x "${basepath}/htdocs/bin/console" ]; then cd "${basepath}/htdocs/" && $PHP_EXE bin/console cache:clear; fi
if [ -x "${basepath}/htdocs/bin/console" ]; then cd "${basepath}/htdocs/" && $PHP_EXE bin/console plugin:refresh; fi
if [ -x "${basepath}/htdocs/bin/console" ]; then cd "${basepath}/htdocs/" && $PHP_EXE bin/console asset:install; fi
if [ -x "${basepath}/htdocs/bin/console" ]; then cd "${basepath}/htdocs/" && $PHP_EXE bin/console theme:compile; fi
# if [ -x "${basepath}/htdocs/bin/console" ]; then cd "${basepath}/htdocs/" && $PHP_EXE bin/console media:generate-thumbnails; fi
if [ -x "${basepath}/htdocs/bin/console" ]; then cd "${basepath}/htdocs/" && $PHP_EXE bin/console cache:clear; fi
if [ -x "${basepath}/htdocs/bin/console" ]; then cd "${basepath}/htdocs/" && $PHP_EXE bin/console cache:warmup; fi

echo finished
