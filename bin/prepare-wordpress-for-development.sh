WP_CLI=$1
REPO_DIR=$2
WORDPRESS_DIR=$3

# Create content directories
mkdir -p $WORDPRESS_DIR/wp-content/{uploads,upgrade,plugins}

# Create symlink for main plugin
if [ ! -e $WORDPRESS_DIR/wp-content/plugins/another-wordpress-classifieds-plugin ]; then
  ln -s $REPO_DIR/another-wordpress-classifieds-plugin $WORDPRESS_DIR/wp-content/plugins/another-wordpress-classifieds-plugin
fi

# Create symlinks for premium modules
if [ -e $REPO_DIR/premium-modules ]; then
    PREMIUM_MODULES=`ls -1 $REPO_DIR/premium-modules`

    for PREMIUM_MODULE in $PREMIUM_MODULES; do
      if [ ! -e $WORDPRESS_DIR/wp-content/plugins/$PREMIUM_MODULE ]; then
        ln -s $REPO_DIR/premium-modules/$PREMIUM_MODULE $WORDPRESS_DIR/wp-content/plugins/$PREMIUM_MODULE
      fi
    done
fi

cd $WORDPRESS_DIR

# Install other plugins
plugins='easy-digital-downloads' # wordpress-beta-tester developer debug-bar debug-bar-cron debug-bar-extender log-deprecated-notices rewrite-rules-inspector query-monitor theme-check'

for plugin in $plugins; do
  if ! ($WP_CLI plugin is-installed $plugin); then
    $WP_CLI plugin install $plugin --activate
  else
    echo "Plugin $plugin is already installed."
  fi
done

# Remove Hello Dolly
if [ -e $WORDPRESS_DIR/wp-content/plugins/hello.php ]; then
  rm -f $WORDPRESS_DIR/wp-content/plugins/hello.php
fi

# Install twentysixteen theme
if ! ( $WP_CLI theme is-installed twentysixteen ); then
  $WP_CLI theme install twentysixteen --activate
else
  echo "Theme twentysixteen is already installed."
  $WP_CLI theme activate twentysixteen
fi

# Enable plugin debug output and setup permalinks
$WP_CLI option update awpcp-debug true
$WP_CLI option update permalink_structure '/%year%/%monthnum%/%postname%/'

cd $OLDPWD
