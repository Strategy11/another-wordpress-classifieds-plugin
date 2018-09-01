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

                if ( self.store.isValid() ) {
                    self.saveListingInformation();
                }
            } );

            self.$resetButton.click( function( event ) {
                event.preventDefault();

                self.clearListingInformation();
            } );

            self.$element.addClass( 'rendered' );
        },

        saveListingInformation: function() {
            var self = this,
                paymentTerm, data, request;

            paymentTerm  = self.store.getSelectedPaymentTerm();
            creditPlanId = self.store.getSelectedCreditPlanId();

            // TODO: How are other sections going to introduce information here?
            data = $.extend( {},  self.store.getListingFields(), {
                action:            'awpcp_save_listing_information',
                nonce:             $.AWPCP.get( 'save_listing_information_nonce' ),
                transaction_id:    self.store.getTransactionId(),
                ad_id:             self.store.getListingId(),
                user_id:           self.store.getSelectedUserId(),
                categories:        self.store.getSelectedCategoriesIds(),
                payment_term_id:   self.store.getSelectedPaymentTermId(),
                payment_term_type: paymentTerm.type,
                payment_type:      paymentTerm.mode,
                credit_plan:       creditPlanId,
                custom:            self.store.getCustomData(),
                current_url:       document.location.href,
            } );

            // Remove Multiple Region Selector data.
            delete data.regions;

            options = {
                url: $.AWPCP.get( 'ajaxurl' ),
                data: data,
                dataType: 'json',
                method: 'POST',
            };

            // Remove existing error messages.
            self.$element.find( '.awpcp-message.awpcp-error' ).remove();

            request = $.ajax( options ).done( function( data ) {
                if ( 'ok' === data.status && data.redirect_url ) {
                    document.location.href = data.redirect_url;
                    return;
                }

                if ( 'error' === data.status && data.errors ) {
                    self.showErrors( data.errors );
                }
            } );
        },

        showErrors: function( errors ) {
            var self = this, $container;

            $container = self.$element.find( '.awpcp-save-submit-listing-section__edit_mode' );

            $.each( errors, function( index, error ) {
                $container.prepend( '<div class="awpcp-message awpcp-error notice notice-error error"><p>' + error + '</p></div>' );
            } );
        },

        clearListingInformation: function() {
            var self = this, data;

            self.store.clearSections();
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

