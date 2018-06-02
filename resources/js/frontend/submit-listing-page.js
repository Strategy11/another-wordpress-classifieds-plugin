/*global AWPCP*/
AWPCP.run( 'awpcp/frontend/submit-listing-page', [
    'jquery',
    'awpcp/frontend/submit-listing-data-store',
    'awpcp/frontend/order-section-controller',
    'awpcp/frontend/listing-fields-section-controller',
], function( $, Store, OrderSectionController, ListingFieldsSectionController ) {
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
        }
    } );

    $( function() {
        var store = new Store();

        var sections = {
            'OrderSection': new OrderSectionController( AWPCPSubmitListingPageSections[0], store ),
            'ListingFields': new ListingFieldsSectionController( AWPCPSubmitListingPageSections[1], store ),
        };

        var page = new Page( store, sections, $( '.awpcp-submit-listing-page-form' ) );

        store.listener = page;
        store.refresh();
    } );
} );
