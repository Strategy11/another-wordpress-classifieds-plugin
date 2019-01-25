/*global AWPCP, _*/
AWPCP.define( 'awpcp/categories-selector-helper', [
    'jquery'
],
function( $ ) {
    var CategoriesSelectorHelper = function( selectedCategoriesIds, categoriesHierarchy, paymentTerms ) {
        var self = this, model;

        this.allCategories         = [];
        this.allCategoriesIds      = [];
        this.selectedCategoriesIds = selectedCategoriesIds;
        this.registry = {};
        this.hierarchy = {};
        this.parents = {};
        this.paymentTerms = paymentTerms;

        var walk = function( parent, level ) {
            if ( typeof categoriesHierarchy[ parent ] === 'undefined' ) {
                return;
            }

            if ( typeof self.hierarchy[ parent ] === 'undefined' ) {
                self.hierarchy[ parent ] = [];
            }

            _.each( categoriesHierarchy[ parent ], function( category ) {
                model = {
                    id: category.term_id,
                    text: ' '.repeat( 3 * level ) + category.name.replace( /&amp;/g, '&' ),
                    disabled: category.disabled || false
                };

                self.allCategories.push( model );
                self.allCategoriesIds.push( model.id );
                self.hierarchy[ parent ].push( model );
                self.registry[ category.term_id ] = model;
                self.parents[ category.term_id ] = parent;

                walk( model.id, level + 1 );
            } );
        };

        walk( 'root', 0 );
    };

    $.extend( CategoriesSelectorHelper.prototype, {
        getAllCategories: function() {
            return this.allCategories;
        },

        getCategoriesAncestors: function( categories ) {
            var ancestors = [];
            var category;

            for ( var i = 0; i < categories.length; i = i + 1 ) {
                category = categories[ i ];

                do {
                    if ( ancestors.indexOf( category ) === -1 ) {
                        ancestors.push( category );
                    }

                    category = this.getCategoryParent( category );
                } while( category && category !== 'root' );
            }

            return ancestors;
        },

        getCategory: function( category ) {
            return this.registry[ category ];
        },

        getCategoryParent: function( category ) {
            return this.parents[ category ];
        },

        getCategoryChildren: function( parent ) {
            if ( typeof this.hierarchy[ parent ] !== 'undefined' ) {
                return this.hierarchy[ parent ];
            } else {
                return [];
            }
        },

        updateSelectedCategories: function( selectedCategoriesIds ) {
            this.selectedCategoriesIds = selectedCategoriesIds;
        },

        getCategoriesThatCanBeSelectedTogether: function() {
            var self = this;

            var allowedPaymentTerms = _.compact( _.map( _.keys( self.paymentTerms ), function( paymentTermKey ) {
                var paymentTerm = self.paymentTerms[ paymentTermKey ];

                // No explicit list of categories means all categories are allowed.
                if ( paymentTerm.categories.length > 0 && _.difference( self.selectedCategoriesIds, paymentTerm.categories ).length !== 0 ) {
                    return null;
                }

                if ( paymentTerm.numberOfCategoriesAllowed > 0 && paymentTerm.numberOfCategoriesAllowed <= self.selectedCategoriesIds.length ) {
                    return null;
                }

                return paymentTerm;
            } ) );

            var categoriesThatCanBeSelectedTogether = [];

            for ( var i = allowedPaymentTerms.length - 1; i >= 0; i = i - 1 ) {
                if ( allowedPaymentTerms[ i ].categories.length === 0 ) {
                    categoriesThatCanBeSelectedTogether = self.allCategoriesIds;
                    break;
                }

                categoriesThatCanBeSelectedTogether = _.union(
                    categoriesThatCanBeSelectedTogether,
                    allowedPaymentTerms[ i ].categories
                );
            }

            return categoriesThatCanBeSelectedTogether;
        }
    } );

    return CategoriesSelectorHelper;
} );
