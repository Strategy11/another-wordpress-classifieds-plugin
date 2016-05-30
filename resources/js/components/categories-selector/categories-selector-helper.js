/*global AWPCP, _*/
AWPCP.define( 'awpcp/categories-selector-helper', [
    'jquery',
    'awpcp/category-item-model'
],
function( $, CategoryItemModel ) {
    var CategoriesSelectorHelper = function( hierarchy, selectionMatrix ) {
        var self = this, parent, model;

        this.all = [];
        this.selectedCategories = [];
        this.registry = {};
        this.hierarchy = {};
        this.parents = {};
        this.selectionMatrix = selectionMatrix;

        _.each( _.keys( hierarchy ), function( key ) {
            parent = ( key == 'root' ? key : parseInt( key, 10 ) );

            self.hierarchy[ parent ] = _.map( hierarchy[ key ], function( category ) {
                model = new CategoryItemModel( {
                    id: category.term_id,
                    name: category.name,
                    price: category.price,
                    selected: false
                } );

                self.all.push( model );
                self.registry[ category.term_id ] = model;
                self.parents[ category.term_id ] = parent;

                return model;
            } );
        } );
    };

    $.extend( CategoriesSelectorHelper.prototype, {
        getAllCategories: function() {
            return this.all;
        },

        getCategoriesAncestors: function( categories ) {
            var ancestors = [];
            var category;

            for ( var i = 0; i < categories.length; i++ ) {
                category = categories[ i ];

                do {
                    if ( ancestors.indexOf( category ) === -1 ) {
                        ancestors.push( category );
                    }

                    category = this.getCategoryParent( category );
                } while( category && category != 'root' );
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

        updateSelectedCategories: function( selectedCategories ) {
            this.selectedCategories = selectedCategories;
        },

        getCategoriesThatCanBeSelectedTogether: function( categories ) {
            var self = this;

            if ( self.selectionMatrix === null ) {
                return categories;
            }

            if ( self.selectedCategories.length === 0 ) {
                return _.map( _.keys( self.selectionMatrix ), function( key ) {
                    return parseInt( key, 10 );
                } );
            } if ( self.selectedCategories.length === 1 ) {
                return self.getCategoriesThatCanBeCombinedWithCategory( self.selectedCategories[0] );
            } else {
                return _.reduce(
                    self.selectedCategories,
                    function( memo, category ) {
                        return _.intersection(
                            memo,
                            self.getCategoriesThatCanBeCombinedWithCategory( category )
                        );
                    },
                    categories
                );
            }
        },

        getCategoriesThatCanBeCombinedWithCategory: function( category ) {
            if ( this.selectionMatrix[ category ] ) {
                return this.selectionMatrix[ category ];
            } else {
                return [];
            }
        }
    } );

    return CategoriesSelectorHelper;
} );
