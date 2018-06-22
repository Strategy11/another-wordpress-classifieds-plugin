/*global AWPCP*/
AWPCP.run( 'awpcp/frontend/submit-listing-page', [
    'jquery',
    'awpcp/frontend/submit-listing-data-store',
    'awpcp/frontend/order-section-controller',
    'awpcp/frontend/listing-fields-section-controller',
    'awpcp/frontend/listing-dates-section-controller',
    'awpcp/frontend/upload-media-section-controller',
    'awpcp/frontend/save-section-controller',
], function(
    $,
    Store,
    OrderSectionController,
    ListingFieldsSectionController,
    ListingDatesSectionController,
    UploadMediaSectionController,
    SaveSectionController
) {
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

            $.each( sections, function( index, data ) {
                if ( typeof self.sections[ data.id ] === 'undefined' ) {
                    return;
                }

                self.store.setSectionStateWithoutRefreshing( data.id, data.state );

                self.sections[ data.id ].reload( data, self.$container );
            } );
        },

        clear: function() {
            var self = this;

            $.each( self.sections, function( index, section ) {
                section.clear();
            } );
        }
    } );

    $( function() {
        var store = new Store();

        var sections = {
            'order':          new OrderSectionController( AWPCPSubmitListingPageSections[0], store ),
            'listing-dates':  new ListingDatesSectionController( AWPCPSubmitListingPageSections[1], store ),
            'listing-fields': new ListingFieldsSectionController( AWPCPSubmitListingPageSections[2], store ),
            'upload-media':   new UploadMediaSectionController( AWPCPSubmitListingPageSections[3], store ),
            'save':           new SaveSectionController( AWPCPSubmitListingPageSections[4], store ),
        };

        store.setSectionStateWithoutRefreshing( AWPCPSubmitListingPageSections[0].id, AWPCPSubmitListingPageSections[0].state );
        store.setSectionStateWithoutRefreshing( AWPCPSubmitListingPageSections[1].id, AWPCPSubmitListingPageSections[1].state );
        store.setSectionStateWithoutRefreshing( AWPCPSubmitListingPageSections[2].id, AWPCPSubmitListingPageSections[2].state );
        store.setSectionStateWithoutRefreshing( AWPCPSubmitListingPageSections[3].id, AWPCPSubmitListingPageSections[3].state );
        store.setSectionStateWithoutRefreshing( AWPCPSubmitListingPageSections[4].id, AWPCPSubmitListingPageSections[4].state );

        var page = new Page( store, sections, $( '.awpcp-submit-listing-page-form' ) );

        store.listener = page;
        store.refresh();
    } );
} );
