/*global AWPCP*/
AWPCP.define( 'awpcp/multiple-value-selector', [ 'jquery' ],
function( $ ) {
    var MultipleValueSelectorViewModel = function( delegate ) {
        this.delegate = delegate;
        this.selectors = this.delegate.getSelectors();
    }

    $.extend( MultipleValueSelectorViewModel.prototype, {
        render: function() {
            var self = this;
            var container, list, selector, element, vm;

            container = this.delegate.getContainerElement( this );
            container.off( 'click.mvs change.mvs' );

            list = container.find( '.awpcp-value-selector-list' );
            list.empty();

            for ( var i = 0; i < this.selectors.length; i++ ) {
                selector = this.selectors[ i ];
                element = element = this.delegate.getSelectorElement( this, selector );
                element.attr( 'data-value-selector', selector );

                vm = this.delegate.getViewModelForSelector( this, selector, element );
                vm.render();

                list.append( element );
            }

            var addButton = container.find( '.awpcp-value-selector-add-button' );

            if ( addButton.length === 0 ) {
                addButton = $( '<a href="#" class="awpcp-value-selector-button awpcp-value-selector-add-button">Add</a>' );
                container.find('.awpcp-value-selector-add-button-placeholder')
                    .replaceWith( addButton );
            }

            if ( ! this.delegate.shouldShowAddButton() ) {
                addButton.hide();
            } else {
                addButton.show();
            }

            container.on( 'click.mvs', '.awpcp-value-selector-add-button', function( event ) {
                event.preventDefault();
                self.delegate.onAddButtonPressed( self );
            } );

            container.on( 'click.mvs', '.awpcp-value-selector-remove-button', function( event ) {
                event.preventDefault();
                var selector = $( event.target ).closest( '[data-value-selector]' ).attr( 'data-value-selector' );
                self.delegate.onRemoveButtonPressed( self, selector );
            } );

            container.on( 'change.mvs', '.awpcp-value-selector-control', {}, function( event ) {
                console.log( 'change.mvs' );
                self.onValueChanged( event );
                return true; // do not prevent propagation or default behaviour
            } );
        },

        onValueChanged: function onValueChanged( event ) {
            var elementId = $( event.target ).attr( 'id' );

            this.delegate.valueChangedInSelector( this );
            this.delegate.getContainerElement().find( '#' + elementId ).focus();
        }
    } );

    return MultipleValueSelectorViewModel;
} );
