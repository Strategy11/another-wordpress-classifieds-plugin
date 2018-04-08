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
