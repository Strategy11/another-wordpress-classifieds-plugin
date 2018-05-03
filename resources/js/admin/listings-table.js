/*global AWPCP */
AWPCP.run( 'awpcp/admin/listings-table', [
    'jquery',
    'awpcp/categories-selector',
], function( $, CategoriesSelector ) {
    $( function() {
        $( '.awpcp-search-mode-dropdown' ).insertBefore( '#post-search-input' ).removeClass( 'awpcp-hidden' );
    } );
} );

