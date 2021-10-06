#!/usr/bin/env bash

INSTALL_DIR=/vagrant/wordpress
DOCUMENT_ROOT=/var/www/html/wordpress

HOME_URL=awpcp.local
BLOG_NAME="AWPCP Test Blog"
ADMIN_EMAIL=wvega@wvega.com

if ! ( rpm --quiet -q remi-release-20 ); then
  wget http://rpms.famillecollet.com/remi-release-20.rpm
  yum -y install remi-release-20.rpm
fi

cat <<PACKAGES > package-list.txt
autojump
git
httpd
mysql
mysql-server
perl-Digest-SHA
php
php-mysql
php-gd
php-pecl-imagick
php-digitalsandwich-Phake
php-pecl-xdebug
php-pecl-xhprof
rsync
postfix
sqlite
svn
unzip
wget
PACKAGES

# TODO: Maybe disable mirros in YUM repos, use baseurl. Sometimes there are a lot of
# network problems with local mirrors.
yum install -y $(cat package-list.txt)
yum install -y --enablerepo=remi php-phpunit-PHPUnit
rm -f package-list.txt

# install WP-CLI
if [ ! -f /usr/bin/wp ]; then
    wget --quiet https://raw.github.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    chmod a+x wp-cli.phar
    mv wp-cli.phar /usr/bin/wp
fi

# Configure Sendmail
systemctl enable postfix
systemctl start postfix

# Configure PHP
sed -i s/'display_errors = Off'/'display_errors = On'/ /etc/php.ini
sed -i s/'html_errors = Off'/'html_errors = On'/ /etc/php.ini
sed -i s/'upload_max_filesize = 2M'/'upload_max_filesize = 40M'/ /etc/php.ini
sed -i s/'post_max_size = 8M'/'post_max_size = 50M'/ /etc/php.ini
sed -i s,';date.timezone =','date.timezone = America/Bogota', /etc/php.ini
sed -i s,';upload_tmp_dir =','upload_tmp_dir = /tmp', /etc/php.ini

# Configure MySQL
systemctl enable mariadb.service
systemctl start mariadb.service

# TODO: test if running these two lines is necessary and avoid running them when is not
mysqladmin --no-beep --silent -uroot password password 2>/dev/null || true
mysqladmin --no-beep create wordpress -uroot -ppassword 2>/dev/null || true

mysql -uroot -ppassword -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'password' WITH GRANT OPTION;"

# Configure Apache
systemctl enable httpd.service
sed -i s/'#ServerName www.example.com:80'/'ServerName localhost:80'/ /etc/httpd/conf/httpd.conf

sed -i s/'^User apache'/'User vagrant'/ /etc/httpd/conf/httpd.conf
sed -i s/'^Group apache'/'Group vagrant'/ /etc/httpd/conf/httpd.conf

mkdir -p /var/lib/php/session/
chown -R vagrant.vagrant /var/lib/php/session/

# Configure WordPress VirtualHost
mkdir -p $INSTALL_DIR
mkdir -p `dirname $DOCUMENT_ROOT`
chown vagrant:vagrant `dirname $DOCUMENT_ROOT`

if [ ! -e $DOCUMENT_ROOT ]; then
  ln -s $INSTALL_DIR $DOCUMENT_ROOT
fi

cat <<VHOST > /etc/httpd/conf.d/wordpress.conf
<VirtualHost *:80>
  ServerName $HOME_URL
  DocumentRoot $DOCUMENT_ROOT

  <Directory $DOCUMENT_ROOT>
    Options FollowSymLinks
    AllowOverride FileInfo Limit Options
    Order allow,deny
    Allow from all
  </Directory>

  <Directory />
    Options FollowSymLinks
    AllowOverride None
  </Directory>

  LogLevel info
  ErrorLog /var/log/httpd/wordpress-error.log
  CustomLog /var/log/httpd/wordpress-access.log combined

  RewriteEngine On
  EnableSendfile Off

  # Insert Extra VirtualHost Configuration Here
</VirtualHost>
VHOST

cat <<HTACCESS > $DOCUMENT_ROOT/.htaccess
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
HTACCESS

# doesn't work over NFS
# chown vagrant:vagrant $DOCUMENT_ROOT/.htaccess
chmod 0644 $DOCUMENT_ROOT/.htaccess

# Install WordPress
cd $INSTALL_DIR

if [ ! -f wp-settings.php ]; then
    su vagrant -c 'wp core download'
fi

if [ ! -f wp-config.php ]; then
    su vagrant -c "wp core config --dbname=wordpress --dbuser=root --dbpass=password --extra-php <<PHP
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('SAVEQUERIES', true);
PHP"
fi

if ! su vagrant -c "wp core is-installed"; then
    su vagrant -c "wp core install --title='$BLOG_NAME' --admin_email='$ADMIN_EMAIL' --admin_user=admin --admin_password=password --url='$HOME_URL/wp-admin/install.php'"
fi

if ! (su vagrant -c "wp user list --fields=user_email" | grep awpcp-john@guerrillamail.com); then
    su vagrant -c "wp user create john awpcp-john@guerrillamail.com --user_pass=password --display_name='John Doe'"
fi

if ! (su vagrant -c "wp user list --fields=user_email" | grep awpcp-jane@guerrillamail.com); then
    su vagrant -c "wp user create jane awpcp-jane@guerrillamail.com --user_pass=password --display_name='Jane Doe'"
fi

# Create symlink for main plugin
if [ ! -e $INSTALL_DIR/wp-content/plugins/another-wordpress-classifieds-plugin ]; then
  ln -s /vagrant/another-wordpress-classifieds-plugin $INSTALL_DIR/wp-content/plugins/another-wordpress-classifieds-plugin
fi

# Create symlinks for premium modules
plugins=`ls -1 /vagrant/premium-modules`

for plugin in $plugins; do
  if [ ! -e $INSTALL_DIR/wp-content/plugins/$plugin ]; then
    ln -s /vagrant/premium-modules/$plugin $INSTALL_DIR/wp-content/plugins/$plugin
  fi
done

# Install other plugins
plugins='easy-digital-downloads' # wordpress-beta-tester developer debug-bar debug-bar-cron debug-bar-extender log-deprecated-notices rewrite-rules-inspector query-monitor theme-check'

for plugin in $plugins; do
  if ! (su vagrant -c "wp plugin is-installed $plugin"); then
    su vagrant -c "wp plugin install $plugin --activate"
  else
    echo "Plugin $plugin is already installed."
  fi
done

# Remove Hello Dolly
if [ -e $INSTALL_DIR/wp-content/plugins/hello.php ]; then
  rm -f $INSTALL_DIR/wp-content/plugins/hello.php
fi

if ! ( su vagrant -c "wp theme is-installed responsive" ); then
  su vagrant -c "wp theme install responsive --activate"
else
  echo "Theme responsive is already installed."
  su vagrant -c "wp theme activate responsive"
fi

su vagrant -c "wp option update awpcp-debug true"
su vagrant -c "wp option update permalink_structure '/%year%/%monthnum%/%postname%/'"

mkdir -p $INSTALL_DIR/wp-content/{uploads,upgrade,plugins}

chmod -R 0777 $INSTALL_DIR/wp-content/uploads
chmod -R 0777 $INSTALL_DIR/wp-content/upgrade
chmod -R 0777 $INSTALL_DIR/wp-content/plugins

# Install WordPress testing framework
export WP_TESTS_DIR=$INSTALL_DIR/tests

DATABASE_NAME=`mysql -e "SHOW DATABASES LIKE 'tests'" -uroot -ppassword -ss`
if [ "$DATABASE_NAME" != "tests" ]; then
    su vagrant -c "wp scaffold plugin-tests akismet"

    mv -f wp-content/plugins/akismet/bin/install-wp-tests.sh /vagrant/scripts/
    mv -f wp-content/plugins/akismet/.travis.yml /vagrant/.travis.yml.new

    chmod 0644 /vagrant/scripts/install-wp-tests.sh
    # for some reason TCP/IP connections to port 3306 on localhost are being rejected
    sed -i "s/ --protocol=tcp//" /vagrant/scripts/install-wp-tests.sh

    su vagrant -c "bash /vagrant/scripts/install-wp-tests.sh tests root password"
fi

if [ -f $WP_TESTS_DIR/wp-tests-config.php ]; then
    sed -i s/'\/tmp\/wordpress'/'\/vagrant\/wordpress'/ $WP_TESTS_DIR/wp-tests-config.php
    sed -i s/'Test Blog'/'$BLOG_NAME'/ $WP_TESTS_DIR/wp-tests-config.php
    sed -i s/admin@example.org/$ADMIN_EMAIL/ $WP_TESTS_DIR/wp-tests-config.php
    sed -i s/example.org/$HOME_URL/ $WP_TESTS_DIR/wp-tests-config.php
fi

# Add environment variables
if ! $( grep WP_TESTS_DIR /home/vagrant/.bash_profile ); then
  cat <<EOF >> /home/vagrant/.bash_profile

export WP_TESTS_DIR=$INSTALL_DIR/tests/
EOF
fi

# Restart services
systemctl restart httpd.service
systemctl restart mysqld.service
