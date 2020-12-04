#CONFIG_FILE="$(dirname $(dirname $WP_TESTS_DIR))/wp-tests-config.php"

CONFIG_FILE=$1

if [ -f "$CONFIG_FILE" ]; then
    DYNAMIC_ABSPATH="getenv( 'ABSPATH' ) ? getenv( 'ABSPATH' ) : sprintf( '%s/src/', dirname( __FILE__ ) )"
    sed -i "s#dirname( __FILE__ ) . '/src/'#$DYNAMIC_ABSPATH#" "$CONFIG_FILE"
fi
