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

            self.$previewButton = self.$element.find( '.awpcp-preview-listing-button' );
            self.$submitButton  = self.$element.find( '.awpcp-submit-listing-button' );
            self.$resetButton   = self.$element.find( '[type="reset"]' );

            self.$previewContainer = self.$element.find( '.awpcp-listing-preview-container' );
            self.$errorsSibling    = self.$submitButton.closest( '.form-submit' );

            self.$previewContainer.hide();

            self.$previewButton.click( function( event ) {
                event.preventDefault();

                if ( self.store.isValid() ) {
                    self.saveListingInformationAndShowPreview();
                }
            } );

            self.$submitButton.click( function( event ) {
                event.preventDefault();

                if ( self.store.isValid() ) {
                    self.saveListingInformationAndRedirect();
                }
            } );

            self.$resetButton.click( function( event ) {
                event.preventDefault();

                self.clearListingInformation();
            } );

            self.$element.addClass( 'rendered' );
        },

        saveListingInformationAndShowPreview: function() {
            var self = this;

            self.saveListingInformation().done( function( data ) {
                self.showListingPreview();
            } );
        },

        saveListingInformation: function() {
            var self     = this,
                deferred = $.Deferred();

            self.clearErrors();

            self.doSaveListingRequest().done( function( data ) {
                if ( 'ok' === data.status && data.redirect_url ) {
                    deferred.resolve( data );
                    return;
                }

                if ( 'error' === data.status && data.errors ) {
                    self.showErrors( data.errors );
                    deferred.reject( data );
                    return;
                }
            } );

            return deferred;
        },

        doSaveListingRequest: function() {
            var self = this,
                paymentTerm, creditPlanId, data, options, request;

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

            return $.ajax( options );
        },

        clearErrors: function() {
            var self = this;

            self.$element.find( '.awpcp-message.awpcp-error' ).remove();
        },

        showErrors: function( errors ) {
            var self = this, $container;

            $.each( errors, function( index, error ) {
                self.$errorsSibling.before( '<div class="awpcp-message awpcp-error notice notice-error error"><p>' + error + '</p></div>' );
            } );
        },

        showListingPreview: function() {
            var self = this;

            self.doGenerateListingPreviewRequest().done( function( data ) {
                if ( 'ok' === data.status && data.preview ) {
                    self.preview = data.preview;

                    self.$previewButton.blur();
                    self.store.refresh();
                }
            } );
        },

        doGenerateListingPreviewRequest: function() {
            var self = this,
                data, options;

            data = {
                action: 'awpcp_generate_listing_preview',
                ad_id:  self.store.getListingId(),
            };

            options = {
                url:      $.AWPCP.get( 'ajaxurl' ),
                data:     data,
                dataType: 'json',
                method:   'POST',
            };

            return $.ajax( options );
        },

        saveListingInformationAndRedirect: function() {
            var self = this;

            self.saveListingInformation().done( function( data ) {
                if ( data.redirect_url ) {
                    document.location.href = data.redirect_url;
                }
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

            if ( self.preview ) {
                self.$previewButton.val( self.$previewButton.data( 'refresh-label' ) );
                self.$previewContainer.html( self.preview ).show();
            }

            self.$element.show();
        },

        reload: function( data ) {
            var self = this;

            self.template = data.template;

            self.$element.removeClass( 'rendered' );
            self.prepareTemplate();
        },

        clear: function() {
        },
    } );

    return SaveSectionController;
} );

