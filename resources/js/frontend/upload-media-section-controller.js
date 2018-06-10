/*global AWPCP*/
AWPCP.define( 'awpcp/frontend/upload-media-section-controller', [
    'jquery',
    'awpcp/media-center',
    'awpcp/settings',
], function( $, MediaCenter, settings ) {
    var UploadMediaSectionController = function( section, store ) {
        var self = this;

        self.id       = section.id;
        self.template = section.template;
        self.store    = store;

        self.selectedPaymentTerm = null;
        self.listing             = null;
    };

    $.extend( UploadMediaSectionController.prototype, {
        render: function( $container ) {
            var self = this;

            if ( ! self.$element ) {
                self.$element = $( '<div></div>' ).appendTo( $container );
            }

            if ( self.shouldUpdateTemplate() ) {
                self.updateSelectedValues();
                self.store.setSectionStateToLoading( self.id );

                return self.store.requestSectionUpdate( self.id );
            }

            self.updateSelectedValues();
            self.prepareTemplate();
        },

        shouldUpdateTemplate: function() {
            var self = this;

            var listing = self.store.getListingId();

            if ( null === listing ) {
                return false;
            }

            if ( listing !== self.listing ) {
                return true;
            }

            return self.store.getSelectedPaymentTermId() !== self.selectedPaymentTerm;
        },

        updateSelectedValues: function() {
            var self = this;

            self.selectedPaymentTerm = self.store.getSelectedPaymentTermId();
            self.listing             = self.store.getListingId();
        },

        prepareTemplate: function() {
            var self = this;

            if ( ! self.$element.hasClass( 'rendered' ) ) {
                self.renderTemplate();
            }

            self.updateTemplate();
        },

        renderTemplate: function() {
            var self = this;

            self.$element = $( self.template ).replaceAll( self.$element );

            self.$element.find( '.awpcp-media-center' ).StartMediaCenter( {
                mediaManagerOptions: settings.get( 'media-manager-data' ),
                mediaUploaderOptions: settings.get( 'media-uploader-data' )
            } );

            self.$element.addClass( 'rendered' );
        },

        updateTemplate: function() {
            var self  = this,
                state = self.store.getSectionState( self.id );

            if ( 'disabled' === state ) {
                return self.showDisabledMode();
            }

            if ( 'loading' === state ) {
                return self.showLoadingMode();
            }

            return self.showEditMode();
        },

        showDisabledMode: function() {
            var self = this;

            self.$element.hide();
        },

        showLoadingMode: function() {
            var self = this;

            self.$element.find( '.awpcp-upload-media-listing-section__loading_mode' ).show();
            self.$element.find( '.awpcp-upload-media-listing-section__edit_mode' ).hide();

            self.$element.show();
        },

        showEditMode: function() {
            var self = this;

            self.$element.find( '.awpcp-upload-media-listing-section__loading_mode' ).hide();
            self.$element.find( '.awpcp-upload-media-listing-section__edit_mode' ).show();

            self.$element.show();
        },

        reload: function( data, $container ) {
            var self = this;

            self.template = data.template;

            self.$element.removeClass( 'rendered' );
            self.prepareTemplate();
        }
    } );

    return UploadMediaSectionController;
} );
