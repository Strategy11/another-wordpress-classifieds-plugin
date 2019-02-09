/*global AWPCP, _*/
AWPCP.define( 'awpcp/categories-selector', [
    'jquery',
    'awpcp/categories-selector-helper',
    'awpcp/settings'
],
function( $, CategoriesSelectorHelper ) {
    /**
     * Select2 custom DataAdapter.
     */

    $.fn.select2.amd.define( 'awpcp/select2/data/array', [
        'select2/utils',
        'select2/data/array'
    ],
    function( Utils, ArrayAdapter ) {
        var CategoriesAdapter = function( $element, options ) {
            this.helper = options.get( 'helper' ) || null;
            this.multiple = options.get( 'multiple' );
            this.cache    = {
                term:  '',
                items: []
            };

            CategoriesAdapter.__super__.constructor.call( this, $element, options );
        };

        Utils.Extend( CategoriesAdapter, ArrayAdapter );

        CategoriesAdapter.prototype.query = function( params, callback ) {
            var self = this;

            self.current( function( current ) {
                var data = [],
                    enabledCategories = null,
                    selectedCategories;

                selectedCategories = $.map( current, function( item ) {
                    return parseInt( item.id, 10 );
                } );

                if ( self.multiple && self.helper ) {
                    enabledCategories = self.helper.getCategoriesThatCanBeSelectedTogether();
                 } else {
                     enabledCategories = self.helper.getAllCategoriesIds();
                 }

                self.$element.find( 'option' ).each( function() {
                    var item  = self.item( $( this ) ),
                        match = self.matches( params, item );

                    if ( match === null ) {
                        return;
                    }

                    if ( self.shouldDisableCategory( item, selectedCategories, enabledCategories ) ) {
                        match = $.extend( {}, match, { disabled: true } );
                    }

                    data.push( match );
                } );

                callback( { results: data } );
            } );
        };

        CategoriesAdapter.prototype.shouldDisableCategory = function( option, selectedCategoriesIds, enabledCategoriesIds ) {
            var id = parseInt( option.id, 10 );

            if ( enabledCategoriesIds === null ) {
                return false;
            }

            if ( _.contains( selectedCategoriesIds, id ) ) {
                return false;
            }

            if ( _.contains( enabledCategoriesIds, id ) ) {
                return false;
            }

            return true;
        };

        CategoriesAdapter.prototype.matches = function( params, item ) {
            var self = this,
                fullName;

            if ( $.trim( params.term ) === '' ) {
                return item;
            }

            if ( self.cache.term !== params.term ) {
                self.cache.term  = params.term;
                self.cache.items = [];
            }

            if ( self.cache.items.indexOf( parseInt( item.parent, 10 ) ) !== -1 ) {
                self.cache.items.push( parseInt( item.id, 10 ) );

                return item;
            }

            if ( item.text.toLowerCase().indexOf( params.term.toLowerCase() ) !== -1 ) {
                self.cache.items.push( parseInt( item.id, 10 ) );

                if ( item.fullName ) {
                    fullName = item.fullName;

                    return $.extend( {}, item, { text: fullName } );
                }

                return item;
            }

            return null;
        };

        return CategoriesAdapter;
    } );

    /**
     * Categories Selector component.
     */

    var CategoriesSelector = function( select, options ) {
        this.$select = $( select );

        this.options = $.extend(
            {},
            window[ 'categories_' + this.$select.attr( 'data-hash' ) ],
            options
        );

        this.options.helper = new CategoriesSelectorHelper(
            this.options.selectedCategoriesIds,
            this.options.categoriesHierarchy,
            this.options.paymentTerms
        );

        this.$select.on( 'change.select2', _.bind( this.onChange, this ) );

        this.render();
    };

    $.extend( CategoriesSelector.prototype, {
        onChange: function() {
            var self = this;

            var categoriesIds = self.getSelectedCategoriesIds();

            this.options.helper.updateSelectedCategories( categoriesIds );

            if ( $.isFunction( self.options.onChange ) ) {
                self.options.onChange( self.getSelectedCategories() );
            }

            $.publish( '/categories/change', [ self.$select, categoriesIds ] );
        },

        getSelectedCategories: function() {
            var self = this;

            return $.map( self.$select.select2( 'data' ), function ( option ) {
                if ( option.id === '' ) {
                    return null;
                }

                var id = parseInt( option.id, 10 );

                return {
                    id: id,
                    name: option.text
                };
            } );
        },

        clearSelectedCategories: function() {
            var self = this;

            self.$select.val( null ).trigger( 'change' );
        },

        getSelectedCategoriesIds: function() {
            var self = this;

            return $.map( self.$select.select2( 'data' ), function ( option ) {
                return parseInt( option.id, 10 );
            } );
        },

        render: function() {
            var self = this;

            var options = $.extend( {}, this.options.select2 );

            var $select = this.$select;
            var $placeholderOption = $select.find( '.awpcp-dropdown-placeholder' );

            options.dataAdapter = $.fn.select2.amd.require( 'awpcp/select2/data/array' );
            options.helper      = this.options.helper;

            options.data = $.map( this.options.helper.getAllCategories(), function( category ) {
                if ( $.inArray( category.id, self.options.selectedCategoriesIds ) >= 0 ) {
                    category.selected = true;
                }

                return category;
            } );

            options.templateSelection = function( selection ) {
                if ( selection.fullName ) {
                    return selection.fullName;
                }

                return selection.text;
            };

            // Single selects require an empty option at the top in order to
            // display the configured placeholder. See https://select2.org/placeholders.
            if ( $placeholderOption.length ) {
                $placeholderOption.text( '' );
                $select.find( 'option' ).not( $placeholderOption ).remove();
            } else {
                $select.empty();
            }

            $select.select2( options );
        }
    } );

    return CategoriesSelector;
} );
