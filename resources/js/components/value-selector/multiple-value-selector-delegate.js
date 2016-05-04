/*global AWPCP, _*/
AWPCP.define( 'awpcp/multiple-value-selector-delegate', [
    'jquery',
    'awpcp/value-selector',
    'awpcp/value-selector-delegate',
    'awpcp/util/guid'
], function( $, ValueSelectorViewModel, ValueSelectorDelegate, guid ) {
    var MultipleValueSelectorDelegate = function( container, options ) {
        var self = this;

        this.container = container;
        this.name = options.name;
        this.multistep = options.multistep;
        this.maxNumberOfSelectors = 3;
        this.messages = {
            'selected-value-not-allowed': 'No payment terms are avaiable for the selected categories. Please change the selected category in this field to see other options.',
        }

        this.allCategories = options.categories;
        this.categoriesParents = this._buildCategoriesParentsHierarchy( options.categories );
        this.availabilityMatrix = undefined;

        this.prepareSelectors( options.selected );

        setTimeout( function() {
            self.broadcastSelectedValue();
        }, 100 );

        $.subscribe( '/category-selector/set-availability-matrix', function( event, matrix ) {
            self.availabilityMatrix = matrix;
        } );

        $.publish( '/category-selector/ready' );
    };

    $.extend( MultipleValueSelectorDelegate.prototype, {
        _buildCategoriesParentsHierarchy: function _buildCategoriesParentsHierarchy( categories ) {
            var categoriesParents = {

            };

            for ( var p in categories ) {
                if ( ! categories.hasOwnProperty( p ) ) {
                    continue;
                }

                for ( var i = 0; i < categories[ p ].length; i++ ) {
                    if ( p == 'root' ) {
                        categoriesParents[ categories[ p ][ i ].term_id ] = p;
                    } else {
                        categoriesParents[ categories[ p ][ i ].term_id ] = parseInt( p, 10 );
                    }
                }
            }

            return categoriesParents;
        },

        prepareSelectors: function prepareSelectors( selected ) {
            this.selectors = [];
            this.selected = {};
            this.errors = {};

            if ( selected.length == 0 ) {
                selected.push( [] );
            }

            for ( var i = 0; i < selected.length; i++ ) {
                this._addSelector( selected[ i ].slice() );
            }
        },

        _addSelector: function _addSelector( selected ) {
            var id = 'vs-' + guid.generate();
            this.selectors.push( id );
            this.selected[ id ] = selected;
        },

        getSelectors: function getSelectors() {
            return this.selectors;
        },

        getContainerElement: function getContainerElement() {
            if ( ! this.container.is( '.awpcp-multiple-value-selector' ) ) {
                var oldContainer = this.container;
                this.container = $( '<div class="awpcp-multiple-value-selector"><ul class="awpcp-value-selector-list awpcp-clearfix"></ul><span class="awpcp-value-selector-add-button-placeholder"></span></div>' );
                oldContainer.replaceWith( this.container );
            }

            return this.container;
        },

        getSelectorElement: function getSelectorElement() {
            return $('<li>');
        },

        getViewModelForSelector: function getViewModelForSelector( model, selector, container ) {
            return new ValueSelectorViewModel(
                new ValueSelectorDelegate( container, {
                    id: selector,
                    name: this.name,
                    label: 'Ad Category',
                    categories: this._filterCategories( this.selected[ selector ], this.allCategories ),
                    selected: this.selected[ selector ],
                    multistep: this.multistep,
                    shouldShowRemoveButton: this.shouldShowRemoveButton(),
                    errors: this.errors[ selector ]
                } )
            );
        },

        _filterCategories: function _filterCategories( selectorSelectedValues, allCategories ) {
            var allSelectedValues = this._getAllSelectedValues();

            var categories = {};
            var category;

            if ( selectorSelectedValues.length == 1 ) {
                selectorSelectedValues = this._getCategoriesHierarchy( selectorSelectedValues );
            }

            for ( var p in allCategories ) {
                if ( ! allCategories.hasOwnProperty( p ) ) {
                    continue;
                }

                categories[ p ] = [];

                for ( var i = 0; i < allCategories[ p ].length; i++ ) {
                    category = allCategories[ p ][ i ];

                    if ( selectorSelectedValues.indexOf( category.term_id ) !== -1 ) {
                        categories[ p ].push( category );
                    } else if ( allSelectedValues.indexOf( category.term_id ) !== -1 ) {
                        continue;
                    } else {
                        categories[ p ].push( category );
                    }
                }

                if ( categories[ p ].length === 0 ) {
                    delete categories[ p ];
                }
            }

            return categories;
        },

        _getAllSelectedValues: function _getAllSelectedValues() {
            var allSelectedValues = [];
            var selected;

            for ( var i = 0; i < this.selectors.length; i++ ) {
                selected = this.selected[ this.selectors[ i ] ];

                if ( selected.length ) {
                    allSelectedValues.push( selected[ selected.length - 1 ] );
                }
            }

            return allSelectedValues;
        },

        _getCategoriesHierarchy: function _getCategoriesHierarchy( categories ) {
            var allCategoriesInHierarchy = [];
            var category;

            for ( var i = 0; i < categories.length; i++ ) {
                category = categories[ i ];

                do {
                    if ( allCategoriesInHierarchy.indexOf( category ) === -1 ) {
                        allCategoriesInHierarchy.push( category );
                    }
                    category = this.categoriesParents[ category ];
                } while( category && category != 'root' );
            }

            return allCategoriesInHierarchy;
        },

        _getSelectorErrors: function _getSelectorErrors( selected, allowed ) {
            if ( typeof allowed == 'undefined' ) {
                return [];
            }

            if ( selected.length == 0 ) {
                return [];
            }

            if ( allowed.indexOf( selected[ selected.length - 1 ] ) !== -1 ) {
                return [];
            }

            return [ this.messages[ 'selected-value-not-allowed' ] ];
        },

        shouldShowAddButton: function shouldShowAddButton() {
            if ( this.selectors.length >= this.maxNumberOfSelectors ) {
                return false;
            }

            return true;
        },

        onAddButtonPressed: function onAddButtonPressed( model ) {
            this._addSelector( [] );
            model.render();
        },

        shouldShowRemoveButton: function shouldShowRemoveButton() {
            return this.selectors.length > 1;
        },

        onRemoveButtonPressed: function onRemoveButtonPressed( model, selector ) {
            var position = this.selectors.indexOf( selector );

            if ( position !== -1 ) {
                this.selectors.splice( position, 1 );
                delete this.selected[ position ];
            }

            model.render();
        },

        valueChangedInSelector: function valueChnagedInSelector( model, selector, target, event ) {
            this._validateSelectedValues( selector );

            model.render();

            this.broadcastSelectedValue();
        },

        _validateSelectedValues: function _validateSelectedValues( selector ) {
            var allSelectedValues = this._getAllSelectedValues();
            var allowedCategories = this._getAllowedCategories( allSelectedValues, this.selected[ selector ] );

            this.errors[ selector ] = this._getSelectorErrors( this.selected[ selector ], allowedCategories );
        },

        _getAllowedCategories: function _getAllowedCategories( allSelectedValues, selectorSelectedValues ) {
            var availabilityMatrix = this.availabilityMatrix;
            var otherSelectedValues = _.difference( allSelectedValues, selectorSelectedValues );
            var allowedCategories, categories;

            if ( otherSelectedValues.length == 0 ) {
                return undefined;
            }

            if ( typeof availabilityMatrix == 'undefined' ) {
                return undefined;
            }

            return _.reduce( otherSelectedValues, function( memo, id ) {
                categories = availabilityMatrix[ id ];

                if ( typeof categories == 'undefined' ) {
                    return [];
                } else if ( memo === null ) {
                    return categories;
                } else {
                    return _.intersection( memo, categories );
                }
            }, null );
        },

        broadcastSelectedValue: function broadcastSelectedValue() {
            var container = this.getContainerElement();
            var values = this._getAllSelectedValues();
            $.publish( '/category/updated', [ container, values ] );
        }
    } );

    return MultipleValueSelectorDelegate;
} );
