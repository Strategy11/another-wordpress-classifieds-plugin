#!/bin/bash

WP_CLI="sudo -u vagrant -- wp"

# Init script for Another WordPress Classifieds Plugin development site
WORDPRESS_VERSIONS='4.0.7 latest'

REPO_DIR=`echo $(dirname $(pwd)) | sed s:/vagrant/www:/srv/www:`
SCRIPTS_DIR="$REPO_DIR/bin"

# remove Nginx configuration file picked up by VVV
if [ -f vvv-nginx.conf ]; then
  rm vvv-nginx.conf
fi

if [ -f vvv-hosts ]; then
  rm vvv-hosts
fi

WORDPRESS_DOMAIN=awpcp.dev
HTDOCS_DIR="$REPO_DIR/htdocs"

# Install WordPress versions and corresponding Nginx configuration files
chmod u+x vvv-install-wordpress.sh

for WORDPRESS_VERSION in $WORDPRESS_VERSIONS ; do
  WORDPRESS_DIR="$HTDOCS_DIR/$WORDPRESS_VERSION"
  WORDPRESS_URL="$WORDPRESS_DOMAIN/$WORDPRESS_VERSION"

  source vvv-install-wordpress.sh "$WP_CLI" $REPO_DIR $SCRIPTS_DIR $WORDPRESS_VERSION $WORDPRESS_DIR $WORDPRESS_URL
done

# Create Nginx configuration file
cd $SCRIPTS_DIR

cp vvv-nginx.conf-sample vvv-nginx.conf
sed -i "s#{vvv_server_name}#$WORDPRESS_DOMAIN#" vvv-nginx.conf
sed -i "s#{vvv_path_to_site_directory}#$HTDOCS_DIR#" vvv-nginx.conf

echo $WORDPRESS_DOMAIN > vvv-hosts

# Install Unit Tests requirements
cd $REPO_DIR
composer install
