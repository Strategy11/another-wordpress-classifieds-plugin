/*global AWPCP, _*/
AWPCP.define( 'awpcp/value-selector', [ 'jquery' ],
function( $ ) {
    var ValueSelectorViewModel = function( delegate ) {
        this.delegate = delegate;
    }

    $.extend( ValueSelectorViewModel.prototype, {
        render: function render() {
            var self = this;
            var container = this.delegate.getContainerElement( this );
            var element, control, options, option;

            container.find( '.awpcp-value-selector-step' ).remove();
            container.off( 'change.vs' );

            for ( var step = 0; step < this.delegate.getSteps(); step++ ) {
                element = this.delegate.getStepElement( this, step );
                control = element.find( '.awpcp-value-selector-control' );
                options = this.delegate.getOptionsForStep( this, step );
                selected = this.delegate.getSelectedValueForStep( this, step );

                for ( var index in options ) {
                    if ( ! options.hasOwnProperty( index ) ) {
                        continue;
                    }

                    option = $( '<option>' )
                        .prop( 'value', options[ index ].value )
                        .html( options[ index ].name );

                    if ( options[ index ].value == selected ) {
                        option.prop( 'selected', true );
                    }

                    control.append( option );
                }

                control.attr( 'data-value-selector-step', step );
                container.find('.awpcp-value-selector-steps-list').append( element );
            }

            var errors = this.delegate.getErrorMessages(),
                errorsList = container.find( '.awpcp-value-selector-errors' ).empty();

            _.each( errors, function( message ) {
                errorsList.append( $( '<p>' + message + '</p>' ) );
            } );

            var removeButton = container.find( '.awpcp-value-selector-remove-button' );

            if ( removeButton.length === 0 ) {
                removeButton = $( '<a href="#" class="awpcp-value-selector-button awpcp-value-selector-remove-button dashicons-before dashicons-no" title="Remove Category"></a>' );
                container.find('.awpcp-value-selector-remove-button-placeholder')
                    .replaceWith( removeButton );
            }

            if ( this.delegate.shouldShowRemoveButton( this ) ) {
                removeButton.show();
            } else {
                removeButton.hide();
            }

            container.on( 'change.vs', '.awpcp-value-selector-control', {}, function(event) {
                self.onValueChanged( event );
                return true; // do not prevent propagation or default behaviour
            } );
        },

        addStep: function addStep() {
            var lastStep = this.steps[ this.steps.length - 1 ];

            this.steps.append( lastStep + 1 );
            this.render();
        },

        removeStep: function removeStep( step ) {
            var position = this.steps.indexOf( step );

            if ( position !== -1 ) {
                this.steps.splice( position, 1 );
            }

            this.render();
        },

        onValueChanged: function onValueChanged( event ) {
            var target = $( event.target );
            var step = parseInt( target.attr( 'data-value-selector-step' ), 10 );
            this.delegate.valueChangedInStep( this, step, target, event );
        },
    } );

    return ValueSelectorViewModel;
} );
