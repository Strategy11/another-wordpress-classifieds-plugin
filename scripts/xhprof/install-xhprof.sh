# Configure XHProf extension
sed -i "s,/tmp,$DOCUMENT_ROOT/xhprof-output," /etc/php.d/xhprof.ini

# Add this to the VirtualHost configuartion
php_admin_value auto_prepend_file $DOCUMENT_ROOT/xhprof/external/header.php

# Configure XHProf GUI
source /vagrant/scripts/install-xhprof.sh

# Install and Configure tracefile-analyser
mkdir -p ~vagrant/bin
wget https://raw.githubusercontent.com/derickr/xdebug/master/contrib/tracefile-analyser.php
echo '#!/bin/php' >> ~vagrant/bin/tracefile-analyser
cat tracefile-analyser.php >> ~vagrant/bin/tracefile-analyser
chmod ug+x ~vagrant/bin/tracefile-analyser
chown -R vagrant.vagrant ~vagrant/bin
rm -f tracefile-analyser.php
