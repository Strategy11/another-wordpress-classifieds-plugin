#!/bin/bash

WP_CLI=$1
REPO_DIR=$2
SCRIPTS_DIR=$3
WORDPRESS_VERSION=$4
WORDPRESS_DIR=$5
WORDPRESS_URL=$6

WEBSITE_TITLE="Another WordPress Classifieds Plugin Development Site (WordPress $WORDPRESS_VERSION)"
DATABASE_NAME=wordpress_$(echo $WORDPRESS_VERSION | sed 's/[.]/_/g')_awpcp

MYSQL_USER=wp
MYSQL_PASSWORD=wp

echo "Commencing $WEBSITE_TITLE Setup"

# Make a database, if we don't already have one
echo "Creating database (if it's not already there)"
mysql -u root --password=root -e "CREATE DATABASE IF NOT EXISTS $DATABASE_NAME"
mysql -u root --password=root -e "GRANT ALL PRIVILEGES ON $DATABASE_NAME.* TO wp@localhost IDENTIFIED BY 'wp';"

# Check for the presence of a `htdocs` folder.
if [ ! -d $WORDPRESS_DIR ]; then
    source $SCRIPTS_DIR/download-wordpress.sh "$WP_CLI" $WORDPRESS_VERSION $WORDPRESS_DIR
elif [ "$WORDPRESS_VERSION" == "latest" ]; then
    source $SCRIPTS_DIR/upgrade-wordpress.sh "$WP_CLI" $WORDPRESS_DIR
fi

if [ ! -f "$WORDPRESS_DIR/wp-config.php" ] || ! $($WP_CLI core is-installed --path=$WORDPRESS_DIR); then
  source $SCRIPTS_DIR/install-wordpress.sh "$WP_CLI" $WORDPRESS_DIR $DATABASE_NAME $MYSQL_USER $MYSQL_PASSWORD $WORDPRESS_URL "$WEBSITE_TITLE"
fi

source $SCRIPTS_DIR/prepare-wordpress-for-development.sh "$WP_CLI" $REPO_DIR $WORDPRESS_DIR

# Let the user know the good news
echo "$WEBSITE_TITLE is now installed";
echo ""
