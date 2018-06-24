/*global AWPCP*/
AWPCP.define( 'awpcp/frontend/order-section-controller', [
    'jquery',
    'awpcp/categories-selector',
    'awpcp/user-selector',
    'awpcp/payment-terms-list',
    'awpcp/credit-plans-list',
    'awpcp/jquery-collapsible',
    'awpcp/jquery-userfield',
    'awpcp/jquery-validate-methods',
], function( $, CategoriesSelector, UserSelector, PaymentTermsList, CreditPlansList ) {
    var OrderSectionController = function( section, store ) {
        var self = this;

        self.id       = section.id;
        self.template = section.template;
        self.store    = store;
    }

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

            self.$listingId = self.$element.find( '[name="listing_id"]' );

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
            self.paymentTermsList = new PaymentTermsList( $container.find( '.awpcp-payment-terms-list' ), {
                onChange: function( paymentTerm ) {
                    self.store.updateSelectedPaymentTerm( paymentTerm );
                }
            } );

            self.categoriesSelector = new CategoriesSelector( $container.find( '.awpcp-category-dropdown' ), {
                onChange: function( categories ) {
                    self.store.updateSelectedCategories( categories );
                }
            } );

            var $userSelect = $container.find( '.awpcp-user-selector' );
            var userSelectorOptions = $.extend( $userSelect.data( 'configuration' ), {
                onChange: function( user ) {
                    self.store.updateSelectedUser( user );
                }
            } );

            self.userSelector = new UserSelector( $userSelect, userSelectorOptions );

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

            $container.on( 'click', '.awpcp-order-submit-listing-section--change-selection-button', function( event ) {
                event.preventDefault();
                self.onChangeSelectionButtonClicked();
            } );

            $.publish( '/awpcp/post-listing-page/order-step/ready', [ self.$element ] );
        },

        updateInitialState: function() {
            var self = this;

            self.store.setListingId( parseInt( self.$listingId.val(), 10 ) );
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
            self.$listingOwner.find( 'span' ).html( self.store.getSelectedUserName() );

            self.$paymentTerm.hide();
            self.$creditPlan.hide();

            var paymentTerm = self.store.getSelectedPaymentTerm();

            if ( paymentTerm ) {
                self.$paymentTerm.html( $( '[data-id="' + paymentTerm.type + '-' + paymentTerm.id + '"]' ).html() ).show();
                self.$paymentTerm.find( 'input' ).prop( 'disabled', true );
                self.$paymentTerm.find( 'label' ).hide();
                self.$paymentTerm.find( '.awpcp-payment-term-price-in-' + paymentTerm.mode ).show();
            }

            var creditPlanSummary = self.store.getSelectedCreditPlanSummary();

            if ( creditPlanSummary ) {
                self.$creditPlan.show().find( 'span' ).html( creditPlanSummary )
            }

            self.$changeSelectionButton.hide();
        },

        updateEditModeTemplate: function() {
            var self = this;

            self.$readModeContainer.hide();
            self.$editModeContainer.show();
        },

        onContinueButtonClicked: function() {
            var self = this;

            if ( ! self.store.getListingId() && self.store.isValid() ) {
                self.store.createEmptyListing();
                self.store.setSectionStateToLoading( self.id );
                return;
            }

            if ( ! self.store.getListingId() ) {
                return;
            }

            self.store.setSectionStateToPreview( self.id );
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
            self.userSelector.clearSelectedUser();
            self.creditPlansList.clearSelectedCreditPlan();
        }
    } );

    return OrderSectionController;
} );
