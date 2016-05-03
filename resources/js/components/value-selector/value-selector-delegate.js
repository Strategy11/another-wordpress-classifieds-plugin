/*global AWPCP*/
AWPCP.define( 'awpcp/value-selector-delegate', ['jquery'],
function( $ ) {
    var ValueSelectorDelegate = function( container, options ) {
        var step = Math.max( 0, options.selected.length - 1 );

        this.container = container;

        this.id = options.id;
        this.name = options.name;
        this.label = options.label;
        this.categories = options.categories;
        this.selected = options.selected;
        this.multistep = options.multistep;

        this._shouldShowRemoveButton = options.shouldShowRemoveButton;
        this._updateSelectorSteps( step, this.selected[ step ] );
    };

    $.extend( ValueSelectorDelegate.prototype, {
        getSteps: function getSteps( model ) {
            return this.steps;
        },

        getContainerElement: function getContainerElement( model ) {
            if ( this.container.find( '.awpcp-value-selector-steps-list' ).length === 0 ) {
                var id = this.id + '-0';
                var label = this.label;

                this.container.append( $( '<label for="' + id + '" class="awpcp-value-selector-label">' + label + '</label>' ) );
                this.container.append( $( '<span class="awpcp-value-selector-remove-button-placeholder"></span>' ) );
                this.container.append( $( '<ul class="awpcp-value-selector-steps-list clearfix"></ul>' ) );
            }

            return this.container;
        },

        getStepElement: function getStepElement( model, step ) {
            var element = $( '<li class="awpcp-value-selector-step"></li>' );
            var id = this.id + '-' + step;
            var name = this.name + '[' + this.id + '][]';

            element.append( '<select id="' + id + '" class="awpcp-value-selector-control" name="' + name + '"></select>' );

            return element;
        },

        getOptionsForStep: function getOptionsForStep( model, step ) {
            if ( this.multistep ) {
                return this._getOptionsForStep( model, step );
            } else {
                return this._getAllOptions();
            }
        },

        _getOptionsForStep: function _getOptionsForStep( model, step ) {
            var parent = step == 0 ? 'root' : this.selected[ step - 1 ];

            categories = this._getCategoryChildren( parent );
            options = [ { name: this._getPlaceholderForStep( step ), value: 0 } ];

            for ( var i = 0; i < categories.length; i++ ) {
                options.push({
                    name: categories[ i ].name,
                    value: categories[ i ].term_id
                });
            }

            return options;
        },

        _getAllOptions: function _getAllOptions() {
            categories = this._getCategoryChildren( 'root' );
            options = [ { name: this._getPlaceholderForStep( 0 ), value: 0 } ];

            return this._prepareOptions( categories, options, 0 );
        },

        _prepareOptions: function _prepareOptions( categories, options, level ) {
            var children, name;

            for ( var i = 0; i < categories.length; i++ ) {
                children = this._getCategoryChildren( categories[ i ].term_id );
                prefix = '&mdash;&nbsp;'.repeat( level );

                options.push({
                    name: prefix + categories[ i ].name,
                    value: categories[ i ].term_id
                });

                this._prepareOptions( children, options, level + 1 );
            }

            return options;
        },

        _getPlaceholderForStep: function _getPlaceholderForStep( step ) {
            var index = Math.min( step, 1 );

            return {
                0: 'Select a Category',
                1: 'Select a Sub-category'
            }[ index ];
        },

        getSelectedValueForStep: function getSelectedValueForStep( model, step ) {
            return this.selected[ step ];
        },

        valueChangedInStep: function valueChangedInStep( model, step, target, event ) {
            this._updateSelectorSteps( step, target.val() );
        },

        _updateSelectorSteps: function _updateSelectorSteps( step, value ) {
            value = parseInt( value, 10 );

            if ( value ) {
                this.selected.splice( step, this.selected.length - step, value );
            } else {
                this.selected.splice( step );
            }

            if ( ! this.multistep ) {
                this.steps = 1;
            } else if ( this._getCategoryChildren( this.selected[ step ] ).length ) {
                this.steps = this.selected.length + 1;
            } else {
                this.steps = Math.max( 1, this.selected.length );
            }
        },

        _getCategoryChildren: function _getCategoryChildren( category ) {
            if ( typeof this.categories[ category ] !== 'undefined' ) {
                return this.categories[ category ];
            } else {
                return [];
            }
        },

        shouldShowRemoveButton: function shouldShowRemoveButton( model ) {
            return this._shouldShowRemoveButton;
        }
    } );

    return ValueSelectorDelegate;
} );
