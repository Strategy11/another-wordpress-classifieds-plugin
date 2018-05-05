/*global AWPCP */
AWPCP.run( 'awpcp/edit-post', [
    'jquery',
    'awpcp/datepicker-field',
    'awpcp/multiple-region-selector-validator',
    'awpcp/jquery-validate-methods'
], function(
    $,
    DatepickerField,
    MultipleRegionsSelectorValidator
) {
    $( function() {
        $( '[datepicker-placeholder]' ).each( function() {
            $.noop( new DatepickerField( $( this ).siblings( '[name]:hidden' ) ) );
        } );

        $( '.awpcp-metabox-tabs' ).on( 'click', '.awpcp-tab a', function() {
            var $link = $( this ),
                $tab = $link.closest( '.awpcp-tab' )
                $container = $tab.closest( '.awpcp-metabox-tabs' );

            $container.find( '.awpcp-tab, .awpcp-tab-panel' ).removeClass( 'awpcp-tab-active awpcp-tab-panel-active' );
            $container.find( $link.attr( 'href' ) ).addClass( 'awpcp-tab-panel-active' );

            $tab.addClass( 'awpcp-tab-active' );
        } );

        $( 'form#post' ).validate({
            messages: $.AWPCP.l10n( 'edit-post-form-fields' ),
            onfocusout: false,
            submitHandler: function( form ) {
                if ( MultipleRegionsSelectorValidator.showErrorsIfUserSelectedDuplicatedRegions( form ) ) {
                    return false;
                }

                if ( MultipleRegionsSelectorValidator.showErrorsIfRequiredFieldsAreEmpty( form ) ) {
                    return false;
                }

                form.submit();
            }
        });
    } );
} );
