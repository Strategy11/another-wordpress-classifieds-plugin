/*global AWPCP*/
AWPCP.run( 'awpcp/custom-validators', [ 'jquery' ],
function( $ ) {
    $( function() {
        if ( typeof $.validator === 'undefined' ) {
            return;
        }

        $.validator.addMethod( 'oneof', function( value, element, params ) {
            if ( this.optional( element ) ) {
                return true;
            }

            if ( $.inArray( value, params ) !== -1 ) {
                return true;
            }

            return false;
        } );
    } );
} );
