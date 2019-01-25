/* globals AWPCP, grecaptcha */
AWPCP.define( 'awpcp/frontend/order-section-controller', [
    'jquery',
    'awpcp/categories-selector',
    'awpcp/user-selector',
    'awpcp/payment-terms-list',
    'awpcp/credit-plans-list',
    'awpcp/jquery-collapsible',
    'awpcp/jquery-userfield',
    'awpcp/jquery-validate-methods'
], function( $, CategoriesSelector, UserSelector, PaymentTermsList, CreditPlansList ) {
    var OrderSectionController = function( section, store ) {
        var self = this;

        self.id       = section.id;
        self.template = section.template;
        self.store    = store;
    };

    $.extend( OrderSectionController.prototype, {
        render: function( $container ) {
            var self = this;

            if ( ! self.$element ) {
                self.renderTemplate( $container );
                self.updateInitialState();
            }

            if ( 'loading' === self.store.getSectionState( self.id ) && self.store.getListingId() ) {
                self.store.setSectionStateToPreview( self.id );
                return;
            }

            self.updateTemplate();
        },

        renderTemplate: function( $container ) {
            var self = this;

            self.$element = $( self.template ).collapsible();

            $container.append( self.$element );

            self.$editModeContainer = $( '.awpcp-order-submit-listing-section__edit_mode' );
            self.$readModeContainer = $( '.awpcp-order-submit-listing-section__read_mode' );

            self.$listingId     = self.$element.find( '[name="listing_id"]' );
            self.$transactionId = self.$element.find( '[name="transaction_id"]' );
            self.$captcha       = self.$element.find( '.awpcp-captcha' );

            self.$listOfSelectedCategories = $( '.awpcp-order-submit-listing-section--selected-categories' );
            self.$listingOwner             = $( '.awpcp-order-submit-listing-section--listing-owner' );
            self.$paymentTerm              = $( '.awpcp-order-submit-listing-section--payment-term' );
            self.$creditPlanLabel          = $( '.awpcp-order-submit-listing-section--credit-plan-label' );
            self.$creditPlan               = $( '.awpcp-order-submit-listing-section--credit-plan' );

            self.$loadingMessage = self.$readModeContainer.find( '.awpcp-order-submit-listing-section--loading-message' );

            self.$changeSelectionButton = self.$readModeContainer.find( '.awpcp-order-submit-listing-section--change-selection-button' );

            // We need to initialize the payment terms list first, so that it
            // can respond to initial events from Categories Selector and User fields.
            //
            // TODO: Is this still the case?
            self.$paymentTermList = self.$editModeContainer.find( '.awpcp-payment-terms-list' );
            self.paymentTermsList = new PaymentTermsList( self.$paymentTermList, {
                onChange: function( paymentTerm ) {
                    self.store.updateSelectedPaymentTerm( paymentTerm );
                }
            } );

            self.$categoriesDropdown = $container.find( '.awpcp-category-dropdown' );

            self.categoriesSelector = new CategoriesSelector( self.$categoriesDropdown, {
                onChange: function( categories ) {
                    self.store.updateSelectedCategories( categories );
                }
            } );

            self.$userSelect = $container.find( '.awpcp-user-selector' );

            var userSelectorOptions = $.extend( self.$userSelect.data( 'configuration' ), {
                onChange: function( user ) {
                    var userInformation = self.getUserInformation( user.id );

                    if ( userInformation ) {
                        $.publish( '/user/updated', [ userInformation ] );
                    }

                    self.store.updateSelectedUser( user );
                }
            } );

            self.userSelector = new UserSelector( self.$userSelect, userSelectorOptions );

            self.creditPlansList = new CreditPlansList( $container.find( '.awpcp-credit-plans-table' ), {
                onChange: function( creditPlan ) {
                    self.store.updateSelectedCreditPlan( creditPlan );
                }
            } );

            self.$editModeContainer.find( 'form' ).validate( {
                messages: $.AWPCP.l10n( 'page-place-ad-order' ),
                submitHandler: function( form, event ) {
                    event.preventDefault();

                    self.onContinueButtonClicked();
                }
            } );

            if ( self.paymentTermsList.includesFreePaymentTermOnly() ) {
                self.$paymentTermList.closest( '.awpcp-form-spacer' ).hide();
            }

            $container.on( 'click', '.awpcp-order-submit-listing-section--change-selection-button', function( event ) {
                event.preventDefault();
                self.onChangeSelectionButtonClicked();
            } );

            var selectedCategoriesIds = $.map( self.getSelectedCategories(), function( category ) {
                return category.id;
            } );

            $.publish( '/user/updated', [ self.getSelectedUserInformation() ] );
            $.publish( '/categories/change', [ self.$categoriesDropdown, selectedCategoriesIds ] );
            $.publish( '/awpcp/post-listing-page/order-step/ready', [ self.$element ] );
        },

        getSelectedUserInformation: function() {
            var self = this,
                user = self.userSelector.getSelectedUser();

            return user ? self.getUserInformation( user.id ) : null;
        },

        getUserInformation: function( userId ) {
            var self = this,
                $user = self.$userSelect.find( 'option[value="' + userId + '"]' );

            return $user.length ? $user.data( 'user-information' ) : null;
        },

        getSelectedCategories: function() {
            var self = this;

            return self.categoriesSelector.getSelectedCategories();
        },

        updateInitialState: function() {
            var self = this,
                listingId = parseInt( self.$listingId.val(), 10 ),
                transactionId = self.$transactionId.val();

            if ( listingId ) {
                self.store.setListingId( listingId );
            }

            if ( transactionId ) {
                self.store.setTransactionId( self.$transactionId.val() );
            }

            self.store.updateSelectedPaymentTerm( self.paymentTermsList.getSelectedPaymentTerm() );
            self.store.updateSelectedCategories( self.categoriesSelector.getSelectedCategories() );
            self.store.updateSelectedUser( self.userSelector.getSelectedUser() );
        },

        updateTemplate: function() {
            var self = this,
                state = self.store.getSectionState( self.id );

            if ( 'loading' === state ) {
                self.showLoadingMode();
                return;
            }

            if ( 'preview' === state ) {
                self.showPreviewMode();
                return;
            }

            if ( 'read' === state ) {
                self.showReadingMode();
                return;
            }

            self.updateEditModeTemplate();
        },

        showLoadingMode: function() {
            var self = this;

            self.showReadingMode();
            self.$loadingMessage.show();
        },

        showPreviewMode: function() {
            var self = this;

            self.showReadingMode();

            self.$changeSelectionButton.show();
        },

        showReadingMode: function() {
            var self = this;

            self.$editModeContainer.hide();
            self.$readModeContainer.show();
            self.$loadingMessage.hide();

            self.$listOfSelectedCategories.empty().text( self.store.getSelectedCategoriesNames().join( ', ' ) );

            self.$paymentTerm.hide();
            self.$creditPlan.hide();
            self.$listingOwner.hide();

            var paymentTerm = self.store.getSelectedPaymentTerm();

            if ( paymentTerm ) {
                self.$paymentTerm.html( $( '[data-id="' + paymentTerm.type + '-' + paymentTerm.id + '"]' ).html() ).show();
                self.$paymentTerm.find( 'input' ).prop( 'disabled', true );
                self.$paymentTerm.find( 'label' ).hide();
                self.$paymentTerm.find( '.awpcp-payment-term-price-in-' + paymentTerm.mode ).show();
            }

            var creditPlanSummary = self.store.getSelectedCreditPlanSummary();

            if ( creditPlanSummary ) {
                self.$creditPlan.show().find( 'span' ).html( creditPlanSummary );
            }

            if ( self.store.getSelectedUserId() ) {
                self.$listingOwner.find( 'span' ).html( self.store.getSelectedUserName() );
                self.$listingOwner.show();
            }

            self.$changeSelectionButton.hide();
        },

        updateEditModeTemplate: function() {
            var self = this;

            self.$readModeContainer.hide();
            self.$editModeContainer.show();

            if ( self.store.getListingId() ) {
                self.$captcha.hide();
            }
        },

        onContinueButtonClicked: function() {
            var self = this;

            if ( ! self.store.getListingId() && self.store.isValid() ) {
                self.createEmptyListing();
                self.store.setSectionStateToLoading( self.id );
                return;
            }

            if ( ! self.store.getListingId() ) {
                return;
            }

            self.store.setSectionStateToPreview( self.id );
        },

        createEmptyListing: function() {
            var self = this, request, paymentTerm, creditPlanId, data, options;

            paymentTerm   = self.store.getSelectedPaymentTerm();
            creditPlanId  = self.store.getSelectedCreditPlanId();

            data = {
                action:                    'awpcp_create_empty_listing',
                nonce:                     $.AWPCP.get( 'create_empty_listing_nonce' ),
                categories:                self.store.getSelectedCategoriesIds(),
                payment_term_id:           paymentTerm.id,
                payment_term_type:         paymentTerm.type,
                payment_term_payment_type: paymentTerm.mode,
                credit_plan:               creditPlanId,
                user_id:                   self.store.getSelectedUserId(),
                custom:                    self.store.getCustomData(),
                current_url:               document.location.href
            };

            data = $.extend( data, self.getCaptchaFields() );

            options = {
                url: $.AWPCP.get( 'ajaxurl' ),
                data: data,
                dataType: 'json',
                method: 'POST'
            };

            // Remove existing error messages.
            self.$element.find( '.awpcp-message.awpcp-error' ).remove();

            request = $.ajax( options ).done( function( data ) {
                if ( 'ok' === data.status && data.redirect_url ) {
                    document.location.href = data.redirect_url;
                    return;
                }

                if ( 'ok' === data.status ) {
                    self.store.setTransactionId( data.transaction );
                    self.store.setListingId( data.listing.ID );
                }

                if ( 'error' === data.status && data.errors ) {
                    self.showErrors( data.errors );
                    self.store.setSectionStateToPreview( self.id );
                }

                var reCAPTCHA = self.$captcha.find( '.awpcp-recaptcha' ).attr( 'data-recaptcha-widget-id' );

                if ( undefined !== reCAPTCHA ) {
                    grecaptcha.reset( reCAPTCHA );
                }
            } );
        },

        getCaptchaFields: function() {
            var self = this;

            if ( self.$captcha.find( '[name="captcha-hash"]' ).length ) {
                return {
                    captcha:        self.$captcha.find( '[name="captcha"]' ).val(),
                    'captcha-hash': self.$captcha.find( '[name="captcha-hash"]' ).val()
                };
            }

            if ( self.$captcha.find( '[name="g-recaptcha-response"]' ).length ) {
                return {
                    'g-recaptcha-response': self.$captcha.find( '[name="g-recaptcha-response"]' ).val()
                };
            }

            return {};
        },

        onChangeSelectionButtonClicked: function() {
            var self = this;

            self.store.setSectionStateToEdit( self.id );
        },

        reload: function() {
        },

        clear: function() {
            var self = this;

            if ( 'read' === self.store.getSectionState( self.id ) ) {
                return;
            }

            self.paymentTermsList.clearSelectedPaymentTerm();
            self.categoriesSelector.clearSelectedCategories();
            self.creditPlansList.clearSelectedCreditPlan();

            self.updateInitialState();

            self.store.setSectionStateToEdit( self.id );
        },

        showErrors: function( errors ) {
            var self = this, $container;

            $container = self.$element.find( '.awpcp-order-submit-listing-section__read_mode .form-submit' );

            $.each( errors, function( index, error ) {
                $container.before( '<div class="awpcp-message awpcp-error notice notice-error error"><p>' + error + '</p></div>' );
            } );
        }
    } );

    return OrderSectionController;
} );
