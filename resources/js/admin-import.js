/*global AWPCP */
AWPCP.run('awpcp/admin-import', ['jquery', 'awpcp/jquery-userfield'], function($) {
    $(function() {
        $('#awpcp-importer-start-date, #awpcp-importer-end-date').datepicker({
            changeMonth: true,
            changeYear: true
        });

        $( '#awpcp-importer-auto-assign-user' ).change( function() {
            var autoAssignUserControl = $( this );
            var importUserControl = $( '#awpcp-importer-user' );
            var method = $.fn.prop ? 'prop' : 'attr';

            if ( ! autoAssignUserControl[ method ]( 'checked' ) ) {
                importUserControl[ method ]( 'disabled', $.fn.prop ? true : 'disabled' );
            } else if ( $.fn.prop ) {
                importUserControl.prop( 'disabled', false );
            } else {
                importUserControl.removeAttr( 'disabled' );
            }
        }).change();

        $('#awpcp-importer-user').userfield();
    });
});
