/*global AWPCP*/
AWPCP.define( 'awpcp/frontend/save-section-controller', [
    'jquery',
], function( $ ) {
    var SaveSectionController = function( section, store ) {
        var self = this;

        self.id       = section.id;
        self.template = section.template;
        self.store    = store;
    };

    $.extend( SaveSectionController.prototype, {
        render: function( $container ) {
            var self = this;

            if ( ! self.$element ) {
                self.$element = $( '<div></div>' ).appendTo( $container );
            }

            if ( self.shouldUpdateSectionState() ) {
                self.updateSelectedValues();

                return self.store.setSectionStateToEdit( self.id );
            }

            self.prepareTemplate();
        },

        shouldUpdateSectionState: function() {
            var self = this;

            var listing = self.store.getListingId();

            if ( null === listing ) {
                return false;
            }

            return listing !== self.listing;
        },

        updateSelectedValues: function() {
            var self = this;

            self.listing = self.store.getListingId();
        },

        prepareTemplate: function() {
            var self = this;

            if ( ! self.$element.hasClass( 'rendered' ) ) {
                self.renderTemplate();
            }

            var state = self.store.getSectionState( self.id );

            if ( 'disabled' === state ) {
                return self.showDisabledMode();
            }

            self.showEditMode();
        },

        renderTemplate: function() {
            var self = this;

            self.$element = $( self.template ).replaceAll( self.$element );

            self.$submitButton = self.$element.find( ':submit' );
            self.$resetButton  = self.$element.find( '[type="reset"]' );

            self.$submitButton.click( function( event ) {
                event.preventDefault();

                self.store.saveListingInformation();
            } );

            self.$resetButton.click( function( event ) {
                event.preventDefault();

                self.store.clearListingInformation();
            } );

            self.$element.addClass( 'rendered' );
        },

        showDisabledMode: function() {
            var self = this;

            self.$element.hide();
        },

        showEditMode: function() {
            var self = this;

            self.$element.show();
        },

        reload: function() {
            var self = this;

            self.prepareTemplate();
        },

        clear: function() {
        },
    } );

    return SaveSectionController;
} );

