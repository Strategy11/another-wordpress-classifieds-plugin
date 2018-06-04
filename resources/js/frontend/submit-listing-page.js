/*global AWPCP*/
AWPCP.run( 'awpcp/frontend/submit-listing-page', [
    'jquery',
    'awpcp/frontend/submit-listing-data-store',
    'awpcp/frontend/order-section-controller',
    'awpcp/frontend/listing-fields-section-controller',
    'awpcp/frontend/upload-media-section-controller',
], function( $, Store, OrderSectionController, ListingFieldsSectionController, UploadMediaSectionController ) {
    var Page = function( store, sections, $container ) {
        var self = this;

        self.store      = store;
        self.sections   = sections;
        self.$container = $container;
    };

    $.extend( Page.prototype, {
        render: function() {
            var self = this;

            $.each( self.sections, function( index, section ) {
                section.render( self.$container );
            } );
        },

        reload: function( sections ) {
            var self = this;

            console.log( 'reloading...', sections );

            $.each( sections, function( index, data ) {
                console.log( self.sections[ data.id ], index, data, self.sections );
                if ( typeof self.sections[ data.id ] === 'undefined' ) {
                    return;
                }

                self.store.setSectionStateWithoutRefreshing( data.id, data.state );

                self.sections[ data.id ].reload( data, self.$container );
            } );
        }
    } );

    $( function() {
        var store = new Store();

        var sections = {
            'order':          new OrderSectionController( AWPCPSubmitListingPageSections[0], store ),
            'listing-fields': new ListingFieldsSectionController( AWPCPSubmitListingPageSections[1], store ),
            'upload-media':   new UploadMediaSectionController( AWPCPSubmitListingPageSections[2], store ),
        };

        store.setSectionStateWithoutRefreshing( AWPCPSubmitListingPageSections[2].id, AWPCPSubmitListingPageSections[2].state );

        var page = new Page( store, sections, $( '.awpcp-submit-listing-page-form' ) );

        store.listener = page;
        store.refresh();
    } );
} );
