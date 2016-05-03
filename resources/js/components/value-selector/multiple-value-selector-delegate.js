/*global AWPCP*/
AWPCP.define( 'awpcp/multiple-value-selector-delegate', [
    'jquery',
    'awpcp/value-selector',
    'awpcp/value-selector-delegate',
    'awpcp/util/guid'
], function( $, ValueSelectorViewModel, ValueSelectorDelegate, guid ) {
    var MultipleValueSelectorDelegate = function( container, options ) {
        this.container = container;
        this.name = options.name;
        this.categories = options.categories;
        this.maxNumberOfSelectors = 3;

        this.init( options );
    };

    $.extend( MultipleValueSelectorDelegate.prototype, {
        init: function init( options ) {
            this.selectors = [];
            this.selected = {};

            if ( options.selected.length == 0 ) {
                options.selected.push( [] );
            }

            for ( var i = 0; i < options.selected.length; i++ ) {
                this._addSelector( options.selected[ i ].slice() );
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
                    categories: this._filterCategories( selector, this.categories ),
                    selected: this.selected[ selector ],
                    multistep: true,
                    shouldShowRemoveButton: this.shouldShowRemoveButton()
                } )
            );
        },

        _filterCategories: function _filterCategories( selector, allCategories ) {
            var categories = {};
            var allSelectedValues = this._getAllSelectedValues();
            var selectorSelectedValues = this.selected[ selector ];
            var category;

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
                        console.log( 'ignored', p, category );
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

        shouldShowAddButton: function shouldShowAddButton() {
            return this.selectors.length < this.maxNumberOfSelectors;
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
            var container = this.getContainerElement();
            var values = this._getAllSelectedValues();

            model.render();

            $.publish( '/category/updated', [ container, values ] );
        }
    } );

    return MultipleValueSelectorDelegate;
} );
