<?php
/**
 * Code executed before PHPUnit executes the test suite.
 *
 * @package AWPCP/Tests
 */

// phpcs:disable Squiz.PHP.CommentedOutCode.Found
// phpcs:disable Squiz.Commenting.InlineComment.SpacingBefore
// phpcs:disable Squiz.Commenting.InlineComment.InvalidEndChar
// phpcs:disable Squiz.Commenting.InlineComment.SpacingAfter

echo 'Welcome to the Test Suite' . PHP_EOL;
echo 'Version: 1.0' . PHP_EOL . PHP_EOL;

$GLOBALS['wp_tests_options'] = array(
    'active_plugins' => array( 'another-wordpress-classifieds-plugin/awpcp.php' ),
);

define( 'WP_TESTS_DATA_DIR', dirname( __FILE__ ) . '/data' );

define( 'AWPCP_DIR', dirname( __DIR__ ) );
define( 'AWPCP_URL', 'https://example.org/wp-content/plugins/another-wordpress-classifieds-plugin' );

require AWPCP_DIR . '/vendor/antecedent/patchwork/Patchwork.php';
require AWPCP_DIR . '/vendor/autoload.php';

Phake::setClient( Phake::CLIENT_PHPUNIT6 );

require AWPCP_DIR . '/functions.php';
require AWPCP_DIR . '/includes/functions/assets.php';
require AWPCP_DIR . '/includes/functions/listings.php';
require AWPCP_DIR . '/includes/functions/routes.php';

require_once __DIR__ . '/../../../../wp-load.php';

if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
    require_once getenv( 'WP_DEVELOP_DIR' ) . 'tests/phpunit/includes/bootstrap.php';
} else {
    require_once '../../../../tests/phpunit/includes/bootstrap.php';
}

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


// require getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';
// tests_add_filter( 'muplugins_loaded', '_remove_plugin_tables' );
// tests_add_filter( 'plugins_loaded', '_replace_modules_manager' );
// require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';
//
// if ( ! defined( 'AUTH_KEY' ) ) {
//     define( 'AUTH_KEY',         '&!GoA!gK8[[.+-N@(L9i;L4TIxm7ttvTi$Q,<wF-JYkdzfB6I{=WA%M]Sbgs]tu2' );
//     define( 'SECURE_AUTH_KEY',  '^--0oyA3cB*m+;[M_ky]lGc^cL,QyEhKrB1)N_BY<[r=CWL=ZdNp}_8FdM&mSyKa' );
//     define( 'LOGGED_IN_KEY',    'zIY?YEd%F|PL9LI8XHt$$O,y>pFH$1I-0ahSX]4EA/G6eneMs5rNjLFa4A6HV}tF' );
//     define( 'NONCE_KEY',        'E(r6:y|o}<?2mI T&2y|#@H}cN`LJIjPuZ=?s%|`J8j?h<ndhQ3!}h1+&=^-_[/e' );
//     define( 'AUTH_SALT',        'Lg_8XIe#y6[}R6.T?}3)-,C6c^fFoXPCav}vg6&rYY],^@D.8Vq1Jv)[jN*ds5Y0' );
//     define( 'SECURE_AUTH_SALT', '!W;%`Mh}k-+(p/bd,U|mZh3wt?tgFeh3GdpP^Jyzj-1mSVjkCD]0fo+BF),r}-hl' );
//     define( 'LOGGED_IN_SALT',   'IP(1$d1rAJC{t=j+M%^=-lOGN8.Cg9NBmBP#4f;t-gi:ataH~AalNpg8Da%#_1+ ' );
//     define( 'NONCE_SALT',       'i6T~mjOLuy#2uHKQ(En3X79R.iY<{ytF}}a~n7|3fL/;3zA-uj9)J06!4mO<xW4{' );
// }

if ( ! defined( 'OBJECT' ) ) {
    define( 'OBJECT', 'OBJECT' );
}

require dirname( __FILE__ ) . '/includes/shims.php';
require dirname( __FILE__ ) . '/includes/functions.php';
require dirname( __FILE__ ) . '/includes/testcase-awpcp.php';
require dirname( __FILE__ ) . '/includes/testcase-step-decorator.php';
require dirname( __FILE__ ) . '/includes/class-listings-table-search-mode-test-case.php';
require dirname( __FILE__ ) . '/includes/class-container-configuration-test-case.php';

