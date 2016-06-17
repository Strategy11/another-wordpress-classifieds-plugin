AWPCP.define( 'awpcp/payment-terms-list', [ 'jquery', 'awpcp/settings' ],
function( $, settings ) {
    var PaymentTermsList = function( container ) {
        var self = this;

        this.container = container;
        this.state = {
            allPaymentTerms: this.container.find( '.awpcp-payment-terms-list-payment-term' )
        };

        $.subscribe( '/category/updated', _.bind( this.onCategoriesUpdated, this ) );
        $.subscribe( '/user/updated', _.bind( this.onUserUpdated, this ) );

        this.update();
    };

    $.extend( PaymentTermsList.prototype, {
        onCategoriesUpdated: function( event, source, categories ) {
            if ( ! $.contains( source.closest( 'form' ).get( 0 ), this.container.get( 0 ) ) ) {
                return;
            }

            if ( categories === null && ! settings.get( 'hide-all-payment-terms-if-no-category-is-selected' ) ) {
                return;
            }

            if ( $.isArray( categories ) ) {
                this.state.selectedCategories = categories;
            } else {
                this.state.selectedCategories = [ categories ];
            }

            this.update();
        },

        onUserUpdated: function( event, user ) {
            this.state.userPaymentTerms = user.payment_terms;
            this.update();
        },

        update: function() {
            var disabledPaymentTerms = this._getDisabledPaymentTerms();
            var enabledPaymentTerms = this.state.allPaymentTerms.not( disabledPaymentTerms.get() );

            if ( enabledPaymentTerms.find( ':radio:checked' ).length === 0 ) {
                var radio = enabledPaymentTerms.eq( 0 ).find( ':radio:first' );

                if ( radio.prop ) {
                    radio.prop( 'checked', true );
                } else {
                    radio.attr( 'checked', 'checked' );
                }
            }

            enabledPaymentTerms.fadeIn();
            disabledPaymentTerms.fadeOut();
        },

        _getDisabledPaymentTerms: function _getDisabledPaymentTerms() {
            var self = this;

            if ( self.state.selectedCategories === null && settings.get( 'hide-all-payment-terms-if-no-category-is-selected' ) ) {
                return self.state.allPaymentTerms;
            }

            return self.state.allPaymentTerms.filter(function() {
                var paymentTerm = $( this );

                // filter by user
                if ( self.state.userPaymentTerms && ! self._isUserPaymentTerm( paymentTerm ) ) {
                    return true;
                }

                // filter by category
                if ( self.state.selectedCategories && ! self._isPaymentTermAvailableForCategories( paymentTerm ) ) {
                    return true;
                }

                return false;
            });
        },

        _isUserPaymentTerm: function( paymentTerm ) {
            return $.inArray( paymentTerm.attr( 'data-id' ), this.state.userPaymentTerms ) !== -1;
        },

        _isPaymentTermAvailableForCategories: function( paymentTerm ) {
            var paymentTermCategories = $.parseJSON( paymentTerm.attr( 'data-categories' ) );

            // payment terms with no list of associated categories are now assumed to be
            // available for all categories.
            if ( ! $.isArray( paymentTermCategories ) || paymentTermCategories.length === 0 ) {
                return true;
            }

            paymentTermCategories = $.map( paymentTermCategories, function( category ) {
                return parseInt( category, 10 );
            } );

            return _.difference( this.state.selectedCategories, paymentTermCategories ).length === 0;
        }
    } );

    return PaymentTermsList;
} );
