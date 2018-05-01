/*global AWPCP, _*/
AWPCP.define( 'awpcp/categories-selector-helper', [
    'jquery'
],
function( $ ) {
    var CategoriesSelectorHelper = function( selectedCategoriesIds, categoriesHierarchy, paymentTerms ) {
        var self = this, parent, model;

        this.allCategories = [];
        this.selectedCategoriesIds = selectedCategoriesIds;
        this.registry = {};
        this.hierarchy = {};
        this.parents = {};
        this.paymentTerms = paymentTerms;

        _.each( _.keys( categoriesHierarchy ), function( key ) {
            parent = ( key === 'root' ? key : parseInt( key, 10 ) );

            self.hierarchy[ parent ] = _.map( categoriesHierarchy[ key ], function( category ) {
                model = {
                    id: category.term_id,
                    text: category.name
                };

                self.allCategories.push( model );
                self.registry[ category.term_id ] = model;
                self.parents[ category.term_id ] = parent;

                return model;
            } );
        } );
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

        getCategoriesThatCanBeSelectedTogether: function( categories ) {
            var self = this;

            var allowedPaymentTerms = _.compact( _.map( _.keys( self.paymentTerms ), function( paymentTermKey ) {
                var paymentTerm = self.paymentTerms[ paymentTermKey ];

                if ( _.difference( self.selectedCategoriesIds, paymentTerm.categories ).length !== 0 ) {
                    return null;
                }

                if ( paymentTerm.numberOfCategoriesAllowed <= self.selectedCategoriesIds.length ) {
                    return null;
                }

                return paymentTerm;
            } ) );

            var categoriesThatCanBeSelectedTogether = [];

            for ( var i = allowedPaymentTerms.length - 1; i >= 0; i = i - 1 ) {
                if ( allowedPaymentTerms[ i ].categories.length === 0 ) {
                    categoriesThatCanBeSelectedTogether = categories;
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
