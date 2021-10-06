# Configure XDebug
if ! (grep 'profiler_enable = 0' /etc/php.d/xdebug.ini); then
    cat <<XDEBUG > /etc/php.d/xdebug.ini
xdebug.profiler_enable = 0
xdebug.profiler_enable_trigger = 1
xdebug.profiler_output_dir = $DOCUMENT_ROOT/xdebug-profile
xdebug.auto_trace = 0
xdebug.trace_enable_trigger = 1
xdebug.trace_output_dir = $DOCUMENT_ROOT/xdebug-trace
xdebug.trace_format = 1
XDEBUG
fi

# Install and Configure Webgrind
cd $DOCUMENT_ROOT
wget https://webgrind.googlecode.com/files/webgrind-release-1.0.zip
unzip webgrind-release-1.0.zip
rm webgrind-release-1.0.zip
cd webgrind

sed -i "s,\$profilerDir = '/tmp',\$profilerDir = '$DOCUMENT_ROOT/xdebug'," config.php
