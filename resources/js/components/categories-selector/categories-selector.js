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

            CategoriesAdapter.__super__.constructor.call( this, $element, options );
        };

        Utils.Extend( CategoriesAdapter, ArrayAdapter );

        CategoriesAdapter.prototype.query = function( params, callback ) {
            var self = this;

            if ( ! self.helper ) {
                CategoriesAdapter.__super__.query.call( self, params, callback );
                return;
            }

            CategoriesAdapter.__super__.current.call( self, function( current ) {
                var selectedCategories = $.map( current, function( option ) {
                    return parseInt( option.id, 10 );
                } );

                CategoriesAdapter.__super__.query.call( self, params, function( data ) {
                    var enabled = self.helper.getCategoriesThatCanBeSelectedTogether( selectedCategories );

                    data.results = $.map( data.results, function( option ) {
                        var newOption = $.extend( {}, option ),
                            id = parseInt( option.id, 10 );

                        if ( ! _.contains( selectedCategories, id ) && ! _.contains( enabled, id ) ) {
                            newOption.disabled = true;
                        }

                        return newOption;
                    } );

                    callback( data );
                } );
            } );
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

        if ( this.options.mode === 'advanced' ) {
            return this.initAdvancedMode();
        }

        return this.initBasicMode();
    };

    $.extend( CategoriesSelector.prototype, {
        initAdvancedMode: function() {
            this.options.helper = new CategoriesSelectorHelper(
                this.options.selectedCategoriesIds,
                this.options.categoriesHierarchy,
                this.options.paymentTerms
            );

            this.initBasicMode();
        },

        initBasicMode: function() {
            this.$select.on( 'change.select2', _.bind( this.onChange, this ) );

            this.render();
        },

        onChange: function() {
            var self = this;

            var categoriesIds = self.getSelectedCategoriesIds();

            if ( this.options.helper ) {
                this.options.helper.updateSelectedCategories( categoriesIds );
            }

            if ( $.isFunction( self.options.onChange ) ) {
                self.options.onChange( self.getSelectedCategories() );
            }

            $.publish( '/categories/change', [ self.$select, categoriesIds ] );
        },

        getSelectedCategories: function() {
            var self = this;

            return $.map( self.$select.select2( 'data' ), function ( option ) {
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

            if ( $placeholderOption.length ) {
                $placeholderOption.text( '' );
            }

            if ( this.options.helper ) {
                options.data = $.map( this.options.helper.getAllCategories(), function( category ) {
                    if ( $.inArray( category.id, self.options.selectedCategoriesIds ) >= 0 ) {
                        category.selected = true;
                    }

                    return category;
                } );

                options.dataAdapter = $.fn.select2.amd.require( 'awpcp/select2/data/array' );
                options.helper = this.options.helper;

                $select.empty();
            }

            $select.select2( options );
        }
    } );

    return CategoriesSelector;
} );
