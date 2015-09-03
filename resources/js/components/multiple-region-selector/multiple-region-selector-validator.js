/*global AWPCP*/
AWPCP.define( 'awpcp/multiple-region-selector-validator', [ 'jquery' ],
function( $ ) {
    var MultipleRegionSelectorValidator = function() {};

    $.extend( MultipleRegionSelectorValidator.prototype, {
        showErrorsIfUserSelectedDuplicatedRegions: function( form ) {
            var self = this,

                $form = $( form ),
                $fields = $form.find( '.multiple-region:visible' ),

                multipleRegionSelector = self.getMultipleRegionSelectorInstance( $fields.first() ),
                userSelectedDuplicatedRegions = false;

            if ( typeof multipleRegionSelector === 'undefined' ) {
                return false;
            }

            $fields.each( function() {
                if ( multipleRegionSelector.checkDuplicatedRegionsForField( $( this ).attr( 'id' ), true ) ) {
                    userSelectedDuplicatedRegions = true;
                }
            } );

            return userSelectedDuplicatedRegions;
        },

        getMultipleRegionSelectorInstance: function( $field ) {
            return $field.closest( '.awpcp-multiple-region-selector' ).data( 'RegionSelector' );
        }
    } );

    return new MultipleRegionSelectorValidator();
} );

