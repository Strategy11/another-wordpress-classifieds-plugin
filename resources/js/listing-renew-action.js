AWPCP.run( 'awpcp/listing-renew-action', [ 'jquery', 'awpcp/settings' ],
    function( $, settings ) {
        $( function() {
            $( '.awpcp-user-renew a' ).on( 'click', function( e ) {
                var that = this;
                $( this ).hide();
                $( this ).siblings( '.fa-spinner' ).css( 'display', 'inline-block' );
                var listingId = $( this ).data( 'id' );
                $.post(  settings.get( 'ajaxurl' ), {
                    action: 'awpcp-ad-renew',
                    listing_id: listingId
                }, function( response ) {
                    if ( response.status == 'ok' ) {
                        $( that ).parent( '.awpcp-user-renew' ).html( response[ 0 ] );
                        return false;
                    }
                    $( this ).parent( '.awpcp-user-renew' ).html( response[ 0 ] );
                } );
                e.preventDefault();
                return false;
            } );
        } );
    } );

