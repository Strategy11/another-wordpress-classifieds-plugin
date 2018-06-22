/*global AWPCP*/
AWPCP.define( 'awpcp/frontend/submit-listing-data-store', [
    'jquery',
], function( $ ) {
    var Store = function ( data ) {
        this.data         = data || {};
        this.refreshCalls = 0;
    };

    $.extend( Store.prototype, {
        setSectionStateToPreview: function( sectionId ) {
            var self = this;

            this.setSectionState( sectionId, 'preview' );
        },

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

            self.refreshCalls = self.refreshCalls + 1;

            if ( typeof self.data.sectionsToUpdate === 'undefined' ) {
                self.data.sectionsToUpdate = [];
            }

            self.listener.render();

            self.refreshCalls = self.refreshCalls - 1;

            if ( self.refreshCalls <= 0 && self.data.sectionsToUpdate.length ) {
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

        getSelectedUserId: function() {
            var self = this;

            if ( self.data.user ) {
                return self.data.user.id;
            }

            return '';
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

        getSelectedCreditPlan: function() {
            var self = this;

            if ( self.data.creditPlan ) {
                return self.data.creditPlan;
            }

            return null;
        },

        getSelectedCreditPlanId: function() {
            var self = this;

            if ( self.data.creditPlan ) {
                return self.data.creditPlan.id;
            }

            return null;
        },

        getSelectedCreditPlanSummary: function() {
            var self = this;

            if ( self.data.creditPlan ) {
                return self.data.creditPlan.summary;
            }

            return '';
        },

        getTransactionId: function() {
            var self = this;

            if ( self.data.transaction ) {
                return self.data.transaction;
            }

            return null;
        },

        updateListingFields: function( fields ) {
            var self = this;

            self.data.fields = $.extend( self.data.fields || {}, fields );

            self.refresh();
        },

        setListingId: function( listingId ) {
            var self = this;

            if ( ! listingId ) {
                return;
            }

            self.data.listing = {
                ID: listingId
            };

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
                paymentTerm, data, request;

            paymentTerm  = self.getSelectedPaymentTerm();
            creditPlanId = self.getSelectedCreditPlanId();

            data = {
                action:                    'awpcp_create_empty_listing',
                nonce:                     $.AWPCP.get( 'create_empty_listing_nonce' ),
                categories:                self.getSelectedCategoriesIds(),
                payment_term_id:           paymentTerm.id,
                payment_term_type:         paymentTerm.type,
                payment_term_payment_type: paymentTerm.mode,
                credit_plan:               creditPlanId,
                user_id:                   self.getSelectedUserId(),
                current_url:               document.location.href,
            };

            options = {
                url: $.AWPCP.get( 'ajaxurl' ),
                data: data,
                dataType: 'json',
                method: 'POST',
            };

            request = $.ajax( options ).done( function( data ) {
                if ( 'ok' === data.status && data.redirect_url ) {
                    document.location.href = data.redirect_url;
                    return;
                }

                if ( 'ok' === data.status ) {
                    self.data.listing = data.listing;
                    self.data.transaction = data.transaction;
                    self.refresh();
                }
            } );
        },

        updateSections: function() {
            var self = this,
                data, request;

            if ( self.updateSectionsTimeout ) {
                clearTimeout( self.updateSectionsTimeout );
            }

            data = {
                action: 'awpcp_update_submit_listing_sections',
                sections: self.data.sectionsToUpdate,
                nonce: $.AWPCP.get( 'update_submit_listing_sections_nonce' ),
                listing: self.getListingId(),
            };

            options = {
                url: $.AWPCP.get( 'ajaxurl' ),
                data: data,
                dataType: 'json',
                method: 'POST',
            };

            self.updateSectionsTimeout = setTimeout( function() {
                request = $.ajax( options ).done( function( data ) {
                    if ( 'ok' === data.status ) {
                        self.listener.reload( data.sections );
                    }

                    self.data.sectionsToUpdate = [];
                } );
            }, 250 );
        },

        saveListingInformation: function() {
            var self = this,
                paymentTerm, data, request;

            paymentTerm  = self.getSelectedPaymentTerm();
            creditPlanId = self.getSelectedCreditPlanId();

            // TODO: How are other sections going to introduce information here?
            // TODO: Remove multiple region selector information from listing fields.
            //       We don't need to send that to the server.
            data = $.extend( {},  self.getListingFields(), {
                action: 'awpcp_save_listing_information',
                nonce: $.AWPCP.get( 'save_listing_information_nonce' ),
                transaction_id: self.getTransactionId(),
                ad_id: self.getListingId(),
                user_id: self.getSelectedUserId(),
                categories: self.getSelectedCategoriesIds(),
                payment_term_id: self.getSelectedPaymentTermId(),
                payment_term_type: paymentTerm.type,
                payment_type: paymentTerm.mode,
                credit_plan: creditPlanId,
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

            request = $.ajax( options ).done( function( data ) {
                if ( 'ok' === data.status && data.redirect_url ) {
                    document.location.href = data.redirect_url;
                    return;
                }
            } );
        },

        clearListingInformation: function() {
            var self = this, data;

            data = {
                action: 'awpcp_clear_listing_information',
                nonce: $.AWPCP.get( 'clear_listing_information_nonce' ),
                ad_id: self.getListingId(),
            };

            options = {
                url: $.AWPCP.get( 'ajaxurl' ),
                data: data,
                dataType: 'json',
                method: 'POST',
            };

            request = $.ajax( options ).done( function( data ) {
                if ( 'ok' === data.status ) {
                    self.listener.clear();
                }
            } );
        },
    } );

    return Store;
} );
