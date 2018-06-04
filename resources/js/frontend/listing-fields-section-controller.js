/*global AWPCP*/
AWPCP.define( 'awpcp/frontend/listing-fields-section-controller', [
    'jquery',
    'awpcp/settings',
    'awpcp/restricted-length-field',
    'awpcp/multiple-region-selector-validator',
    'awpcp/jquery-validate-methods',
], function( $, settings, RestrictedLengthField, MultipleRegionsSelectorValidator ) {
    var ListingFieldsSectionController = function( section, store ) {
        var self = this;

        self.id       = section.id;
        self.template = section.template;
        self.store    = store;

        self.selectedCategories  = [];
        self.selectedPaymentTerm = null;

        self.updater = null;
    };

    $.extend( ListingFieldsSectionController.prototype, {
        render: function( $container ) {
            var self = this;

            if ( ! self.$element ) {
                self.$element = $( '<div></div>' ).appendTo( $container );
            }

            if ( self.shouldUpdateTemplate() ) {
                self.updateSelectedValues();

                return self.getUpdatedTemplate();
            }

            self.updateSelectedValues();
            self.prepareTemplate();
        },

        shouldUpdateTemplate: function() {
            var self = this;

            var selectedCategories  = self.store.getSelectedCategoriesIds();
            var selectedPaymentTerm = self.store.getSelectedPaymentTermId();

            if ( 0 === selectedCategories.length || null === selectedPaymentTerm ) {
                return false;
            }

            if ( ! _.isEqual( selectedCategories, self.selectedCategories ) ) {
                return true;
            }

            if ( selectedPaymentTerm !== self.selectedPaymentTerm ) {
                return true;
            }

            return false;
        },

        getUpdatedTemplate: function() {
            var self = this;

            (function( data ) {
                self.template = data.template;
                self.$element.removeClass( 'rendered' );
                self.prepareTemplate();
            })( { template: self.template } );
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

            var data = self.store.getListingFields();

            // References to necessary elements.
            self.$regionsSelector = self.$element.find( '.awpcp-multiple-region-selector' );

            if ( settings.get( 'overwrite-contact-information-on-user-change' ) ) {
                // self.updater = new UserInformationUpdater( self.$element );
                // self.updater.watch();
            }

            // display and control characters allowed for the Ad title
            $.noop( new RestrictedLengthField( self.$element.find( '[name="ad_title"]' ) ) );

            // display and control characters allowed for the Ad details
            $.noop( new RestrictedLengthField( self.$element.find( '[name="ad_details"]' ) ) );

            // Instantiate Multiple Region Selector using the currently selected regions,
            // if any.
            self.$regionsSelector.MultipleRegionSelector( data.regions );

            // TODO: Route this through the store.
            $.publish( '/awpcp/post-listing-page/details-step/ready', [ self.$element ] );

            // var validator = self.$element.find( 'form' ).validate({
            //     messages: $.AWPCP.l10n( 'page-place-ad-details' ),
            //     onfocusout: false,
            //     submitHandler: function( form, event ) {
            //         event.preventDefault();

            //         var $form = $( form );

            //         if ( MultipleRegionsSelectorValidator.showErrorsIfUserSelectedDuplicatedRegions( $form ) ) {
            //             return false;
            //         }

            //         if ( MultipleRegionsSelectorValidator.showErrorsIfRequiredFieldsAreEmpty( $form ) ) {
            //             return false;
            //         }

            //         self.onContinueButtonClicked();
            //     }
            // });

            self.$element.on( 'change', '.awpcp-has-value', function() {
                self.onContinueButtonClicked();
            } );

            self.$element.find( 'form :submit' ).click( function( event ) {
                event.preventDefault();

                self.onContinueButtonClicked();
            } );

            self.$element.addClass( 'rendered' );
        },

        onContinueButtonClicked: function() {
            var self = this,
                data = {};

            self.$element.find( '.awpcp-has-value' ).each( function( index, element ) {
                var $field = $( element );

                data[ $field.attr( 'name' ) ] = $field.val();
            } );

            data.regions = self.$regionsSelector.data( 'RegionSelector' ).getSelectedRegions();

            self.store.updateListingFields( data );
        },

        updateTemplate: function( $container ) {
            var self = this;

            data = self.store.getListingFields();

            $.each( data, function( name, value ) {
                $( '[name="' + name + '"]').val( value );
            } );
        },

        updateSelectedValues: function() {
            var self = this;

            self.selectedCategories  = self.store.getSelectedCategoriesIds();
            self.selectedPaymentTerm = self.store.getSelectedPaymentTermId();
        },

        reload: function() {
        }
    } );

    return ListingFieldsSectionController;
} );
