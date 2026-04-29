<?php
/**
 * Standalone vulnerability test for the regions[] array-key SQL injection
 * (Wordfence-style PoC reproduction).
 *
 * Run: php tests/security/test-regions-sqli.php
 *
 * The script bypasses the full WordPress test bootstrap and instead stubs the
 * minimum surface required to load AWPCP_QueryIntegration.
 */

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'AWPCP_TABLE_AD_REGIONS' ) ) {
    define( 'AWPCP_TABLE_AD_REGIONS', 'wp_awpcp_ad_regions' );
}

if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $tag, $value ) {
        return $value;
    }
}

require_once dirname( __DIR__, 1 ) . '/../includes/listings/class-query-integration.php';

/**
 * Minimal wpdb double that mirrors the real wpdb::prepare() identifier
 * handling closely enough to detect SQL injection breakouts.
 */
class Mock_WPDB {

    public $posts = 'wp_posts';

    public function esc_like( $text ) {
        return addcslashes( $text, '_%\\' );
    }

    public function prepare( $query, ...$args ) {
        if ( count( $args ) === 1 && is_array( $args[0] ) ) {
            $args = $args[0];
        }

        return preg_replace_callback(
            '/%[sdif]/',
            function ( $match ) use ( &$args ) {
                $value = array_shift( $args );

                if ( $match[0] === '%d' ) {
                    return (int) $value;
                }

                if ( $match[0] === '%f' ) {
                    return (float) $value;
                }

                return "'" . str_replace( "'", "''", (string) $value ) . "'";
            },
            $query
        );
    }
}

$db = new Mock_WPDB();

$integration = new AWPCP_QueryIntegration(
    'awpcp_listing',
    'awpcp_category',
    new stdClass(),
    $db
);

$malicious_key = "country` = 1 OR EXTRACTVALUE(1,CONCAT(0x7e,(SELECT user_login FROM wp_users LIMIT 1))) AND `country";

$query = (object) array(
    'query_vars' => array(
        'classifieds_query' => array(
            'regions' => array(
                array( $malicious_key => 'test' ),
            ),
        ),
    ),
);

$clauses = $integration->posts_clauses(
    array( 'join' => '', 'where' => '1=1', 'orderby' => '' ),
    $query
);

$where = $clauses['where'];
$join  = $clauses['join'];

$failures = array();

if ( stripos( $where, 'EXTRACTVALUE' ) !== false ) {
    $failures[] = 'WHERE clause contains EXTRACTVALUE() payload.';
}

if ( stripos( $where, 'user_login' ) !== false ) {
    $failures[] = 'WHERE clause contains injected sub-query (user_login).';
}

if ( preg_match( '/listing_regions\.`country`\s*=\s*1\s+OR/i', $where ) ) {
    $failures[] = 'WHERE clause contains broken-out identifier with OR injection.';
}

echo "----- Resulting JOIN -----\n{$join}\n\n";
echo "----- Resulting WHERE -----\n{$where}\n\n";

$benign_query = (object) array(
    'query_vars' => array(
        'classifieds_query' => array(
            'regions' => array(
                array( 'country' => 'US', 'state' => 'CA' ),
            ),
        ),
    ),
);

$benign_clauses = $integration->posts_clauses(
    array( 'join' => '', 'where' => '1=1', 'orderby' => '' ),
    $benign_query
);

if ( stripos( $benign_clauses['where'], "listing_regions.`country` LIKE '%US%'" ) === false ) {
    $failures[] = 'Benign country search no longer produces expected SQL.';
}

if ( stripos( $benign_clauses['where'], "listing_regions.`state` LIKE '%CA%'" ) === false ) {
    $failures[] = 'Benign state search no longer produces expected SQL.';
}

echo "----- Benign WHERE -----\n{$benign_clauses['where']}\n\n";

if ( empty( $failures ) ) {
    echo "PASS: regions[] array-key SQL injection is mitigated and benign queries still work.\n";
    exit( 0 );
}

echo "FAIL:\n - " . implode( "\n - ", $failures ) . "\n";
exit( 1 );
