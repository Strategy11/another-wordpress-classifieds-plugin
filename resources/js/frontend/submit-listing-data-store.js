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

            self.setSectionStateWithoutRefreshing( sectionId, state );
            self.refresh();
        },

        setSectionStateWithoutRefreshing: function( sectionId, state ) {
            var self = this;

            if ( typeof self.data.sections === 'undefined' ) {
                self.data.sections = {};
            }

            if ( typeof self.data.sections[ sectionId ] === 'undefined' ) {
                self.data.sections[ sectionId ] = {};
            }

            self.data.sections[ sectionId ].state = state;
        },

        setSectionStateToEdit: function( sectionId ) {
            var self = this;

            self.setSectionState( sectionId, 'edit' );
        },

        setSectionStateToLoading: function( sectionId ) {
            var self = this;

            self.setSectionState( sectionId, 'loading' );
        },

        refresh: function() {
            var self = this;

            self.data.sectionsToUpdate = [];

            self.listener.render();

            if ( self.data.sectionsToUpdate.length ) {
                self.updateSections();
            }
        },

        getSectionState: function( sectionId ) {
            var self = this;

            if ( self.data.sections && self.data.sections[ sectionId ] && self.data.sections[ sectionId ].state ) {
                return self.data.sections[ sectionId ].state;
            }

            return 'edit';
        },

        requestSectionUpdate: function( sectionId ) {
            var self = this;

            self.data.sectionsToUpdate.push( sectionId );
        },

        updateSelectedCategories: function( categories ) {
            var self = this;

            self.data.categories = categories;

            self.refresh()
        },

        getSelectedCategoriesIds: function() {
            var self = this;

            return $.map( self.data.categories || [], function( category ) {
                return category.id;
            } );
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

        getSelectedPaymentTermId: function() {
            var self = this;

            if ( self.data.paymentTerm ) {
                return self.data.paymentTerm.id
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
        },

        updateListingFields: function( fields ) {
            var self = this;

            self.data.fields = $.extend( self.data.fields || {}, fields );

            self.refresh();
        },

        getListingId: function() {
            var self = this;

            if ( self.data.listing ) {
                return self.data.listing.ID;
            }

            return null;
        },

        getListingFields: function() {
            var self = this;

            return self.data.fields || {};
        },

        createEmptyListing: function() {
            var self = this,
                data, request;

            data = {
                action:       'awpcp_create_empty_listing',
                nonce:        $.AWPCP.get( 'create_empty_listing_nonce' ),
                categories:   self.getSelectedCategoriesIds(),
                payment_term: self.getSelectedPaymentTermId(),
            };

            request = $.getJSON( $.AWPCP.get( 'ajaxurl' ), data ).done( function( data ) {
                if ( 'ok' === data.status ) {
                    self.data.listing = data.listing;
                    self.refresh();
                }
            } );
        },

        updateSections: function() {
            var self = this,
                data, request;

            data = {
                action: 'awpcp_update_submit_listing_sections',
                nonce: $.AWPCP.get( 'update_submit_listing_sections_nonce' ),
                listing: self.getListingId(),
            };

            request = $.getJSON( $.AWPCP.get( 'ajaxurl' ), data ).done( function( data ) {
                if ( 'ok' === data.status ) {
                    self.listener.reload( data.sections );
                }
            } );
        }
    } );

    return Store;
} );
