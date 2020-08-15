# Following some instructions from https://github.com/preinheimer/xhprof/blob/master/INSTALL

cd $DOCUMENT_ROOT
wget https://github.com/preinheimer/xhprof/archive/master.zip -O xhprof.zip
unzip xhprof.zip
rm -f xhprof.zip
mv xhprof-master xhprof

cp xhprof/xhprof_lib/config.sample.php xhprof/xhprof_lib/config.php

sed -i s/'myserver'/'AWPCP XHProf Server'/ xhprof/xhprof_lib/config.php
sed -i s/'myapp'/'AWPCP'/ xhprof/xhprof_lib/config.php
sed -i "s,'http://url/to/xhprof/xhprof_html','http://$HOME_URL/xhprof/xhprof_html'," xhprof/xhprof_lib/config.php
sed -i "s/\['serializer'\] = 'php'/['serializer'] = 'json'/" xhprof/xhprof_lib/config.php

sed -i "s/controlIPs = array()/controlIPs = false/" xhprof/xhprof_lib/config.php
sed -i "s,\$controlIPs\[\],//\$controlIPs[]," xhprof/xhprof_lib/config.php


cat <<VHOST > /etc/httpd/conf.d/wordpress-xhprof.conf
<VirtualHost *:80>
  ServerName xh.$HOME_URL
  DocumentRoot $DOCUMENT_ROOT/xhprof/xhprof_html

  <Directory $DOCUMENT_ROOT>
    EnableSendfile Off
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

  php_admin_value auto_prepend_file $DOCUMENT_ROOT/xhprof/external/header.php

  RewriteEngine On
</VirtualHost>
VHOST

mysqladmin create xhprof -uroot -ppassword || true
mysql -uroot -ppassword xhprof <<MYSQL
CREATE TABLE \`details\` (
    \`id\` char(17) NOT NULL,
    \`url\` varchar(255) default NULL,
    \`c_url\` varchar(255) default NULL,
    \`timestamp\` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    \`server name\` varchar(64) default NULL,
    \`perfdata\` MEDIUMBLOB,
    \`type\` tinyint(4) default NULL,
    \`cookie\` BLOB,
    \`post\` BLOB,
    \`get\` BLOB,
    \`pmu\` int(11) unsigned default NULL,
    \`wt\` int(11) unsigned default NULL,
    \`cpu\` int(11) unsigned default NULL,
    \`server_id\` char(3) NOT NULL default 't11',
    \`aggregateCalls_include\` varchar(255) DEFAULT NULL,
    PRIMARY KEY  (\`id\`),
    KEY \`url\` (\`url\`),
    KEY \`c_url\` (\`c_url\`),
    KEY \`cpu\` (\`cpu\`),
    KEY \`wt\` (\`wt\`),
    KEY \`pmu\` (\`pmu\`),
    KEY \`timestamp\` (\`timestamp\`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
MYSQL
