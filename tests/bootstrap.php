<?php
/**
 * Code executed before PHPUnit executes the test suite.
 *
 * @package AWPCP/Tests
 */

echo 'Welcome to the Test Suite' . PHP_EOL;
echo 'Version: 1.0' . PHP_EOL . PHP_EOL;

$GLOBALS['wp_tests_options'] = array(
    'active_plugins' => array( 'another-wordpress-classifieds-plugin/awpcp.php' ),
);

define( 'WP_TESTS_DATA_DIR', dirname( __FILE__ ) . '/data' );

define( 'AWPCP_DIR', dirname( __DIR__ ) );
define( 'AWPCP_URL', 'https://example.org/wp-content/plugins/another-wordpress-classifieds-plugin' );

$patchwork = AWPCP_DIR . '/vendor/antecedent/patchwork/Patchwork.php';
if ( getenv( 'RUNNER_COMPOSER_PATH' ) ) {
    $patchwork = getenv( 'RUNNER_COMPOSER_PATH' ).'vendor/antecedent/patchwork/Patchwork.php';
}
if ( file_exists( $patchwork ) ) {
	require_once $patchwork;
}

require AWPCP_DIR . '/vendor/autoload.php';

Phake::setClient( Phake::CLIENT_PHPUNIT6 );

require_once AWPCP_DIR . '/functions.php';
require_once AWPCP_DIR . '/includes/functions/assets.php';
require_once AWPCP_DIR . '/includes/functions/listings.php';
require_once AWPCP_DIR . '/includes/functions/routes.php';

/**
 * TODO: We probably won't need this if we stop using WordPress testing framework.
 */
function _remove_plugin_tables() {
    awpcp()->installer->uninstall();
}

/**
 * TODO: We probably won't need this if we stop using WordPress testing framework.
 */
function _replace_modules_manager() {
    require dirname( __FILE__ ) . '/includes/class-relaxed-modules-manager.php';
    require dirname( __FILE__ ) . '/includes/class-upgrade-task-handler-tester.php';
    awpcp()->modules_manager = awpcp_relaxed_modules_manager();
}

if ( ! defined( 'OBJECT' ) ) {
    define( 'OBJECT', 'OBJECT' );
}

require_once dirname( __FILE__ ) . '/includes/shims.php';
require_once dirname( __FILE__ ) . '/includes/functions.php';
require_once dirname( __FILE__ ) . '/includes/testcase-awpcp.php';
require_once dirname( __FILE__ ) . '/includes/testcase-step-decorator.php';
require_once dirname( __FILE__ ) . '/includes/class-listings-table-search-mode-test-case.php';
require_once dirname( __FILE__ ) . '/includes/class-container-configuration-test-case.php';

