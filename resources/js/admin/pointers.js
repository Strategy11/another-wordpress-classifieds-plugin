/*global AWPCP*/
AWPCP.run( 'awpcp/admin-pointers', [ 'jquery', 'awpcp/pointers-manager', 'awpcp/settings' ],
function( $, PointersManager, settings ) {
    $( function() {
        var manager, pointers = settings.get( 'pointers' );

        if ( pointers ) {
            manager = new PointersManager( pointers );
            manager.createPointers();
        }
    } );
} );
