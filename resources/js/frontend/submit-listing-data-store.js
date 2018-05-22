/*global AWPCP*/
AWPCP.define( 'awpcp/frontend/submit-listing-data-store', [
    'jquery',
], function( $ ) {
    var Store = function ( data ) {
        this.data = data || {};
    };

    $.extend( Store.prototype, {
        setSectionStateToRead: function( sectionId ) {
            var self = this;

            self.setSectionState( sectionId, 'read' );
        },

        setSectionState: function( sectionId, state ) {
            var self = this;

            if ( typeof self.data.sections === 'undefined' ) {
                self.data.sections = {};
            }

            if ( typeof self.data.sections[ sectionId ] === 'undefined' ) {
                self.data.sections[ sectionId ] = {};
            }

            self.data.sections[ sectionId ].state = state;

            self.refresh();
        },

        setSectionStateToEdit: function( sectionId ) {
            var self = this;

            self.setSectionState( sectionId, 'edit' );
        },

        refresh: function() {
            var self = this;

            self.listener.render();

            console.log( self.data );
        },

        getSectionState: function( sectionId ) {
            var self = this;

            if ( self.data.sections && self.data.sections[ sectionId ] && self.data.sections[ sectionId ].state ) {
                return self.data.sections[ sectionId ].state;
            }

            return 'edit';
        },

        updateSelectedCategories: function( categories ) {
            var self = this;

            self.data.categories = categories;

            self.refresh()
        },

        getSelectedCategoriesNames: function() {
            var self = this;

            return $.map( self.data.categories || [], function( category ) {
                return category.name;
            } );
        },

        updateSelectedUser: function( user ) {
            var self = this;

            self.data.user = user;

            self.refresh();
        },

        getSelectedUserName: function() {
            var self = this;

            if ( self.data.user ) {
                return self.data.user.name;
            }

            return '';
        },

        updateSelectedPaymentTerm: function( paymentTerm ) {
            var self = this;

            self.data.paymentTerm = paymentTerm;

            self.refresh();
        },

        getSelectedPaymentTerm: function() {
            var self = this;

            if ( self.data.paymentTerm ) {
                return self.data.paymentTerm;
            }

            return null;
        },

        getSelectedPaymentTermSummary: function() {
            var self = this;

            if ( self.data.paymentTerm ) {
                return self.data.paymentTerm.summary;
            }

            return '';
        },

        updateSelectedCreditPlan: function( creditPlan ) {
            var self = this;

            self.data.creditPlan = creditPlan;

            self.refresh();
        },

        getSelectedCreditPlanSummary: function() {
            var self = this;

            if ( self.data.creditPlan ) {
                return self.data.creditPlan.summary;
            }

            return '';
        }
    } );

    return Store;
} );
