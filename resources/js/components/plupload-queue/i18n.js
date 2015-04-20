/*global AWPCP, plupload*/
AWPCP.run( 'awpcp/plupload-queue-translation', [ 'awpcp/settings' ],
function( settings ) {
    plupload.addI18n( settings.l10n( 'plupload-queue' ) );
} );
