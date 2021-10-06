WP_CLI=$1
WORDPRESS_DIR=$2

cd $WORDPRESS_DIR

echo "Updating WordPress using WP-CLI"
$WP_CLI core upgrade

cd $OLDPWD
