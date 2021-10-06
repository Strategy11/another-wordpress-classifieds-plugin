WP_CLI=$1
WORDPRESS_DIR=$2
DB_NAME=$3
MYSQL_USER=$4
MYSQL_PASSWORD=$5
WORDPRESS_URL=$6
WEBSITE_TITLE=$7

cd $WORDPRESS_DIR

# Use WP CLI to create a `wp-config.php` file
if [ ! -f "wp-config.php" ]; then
    $WP_CLI core config --dbname="$DB_NAME" --dbuser=$MYSQL_USER --dbpass=$MYSQL_PASSWORD --dbhost="localhost" --extra-php <<PHP
// Match any requests made via xip.io.
if ( isset( \$_SERVER['HTTP_HOST'] ) && preg_match('/^(local.wordpress.)\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(.xip.io)\z/', \$_SERVER['HTTP_HOST'] ) ) {
    define( 'WP_HOME', 'http://' . \$_SERVER['HTTP_HOST'] );
    define( 'WP_SITEURL', 'http://' . \$_SERVER['HTTP_HOST'] );
}

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'SAVEQUERIES', true );
PHP
fi

if ! $($WP_CLI core is-installed); then
    # Use WP CLI to install WordPress
    $WP_CLI core install --url=$WORDPRESS_URL --title="$WEBSITE_TITLE" --admin_user=admin --admin_password=password --admin_email=wvega@wvega.com
fi

# Create john user
if ! ($WP_CLI user list --fields=user_email | grep awpcp-john@guerrillamail.com); then
    $WP_CLI user create john awpcp-john@guerrillamail.com --user_pass=password --display_name='John Doe'
fi

# Create jane user
if ! ($WP_CLI user list --fields=user_email | grep awpcp-jane@guerrillamail.com); then
    $WP_CLI user create jane awpcp-jane@guerrillamail.com --user_pass=password --display_name='Jane Doe'
fi

cd $OLDPWD
