/*global AWPCP*/
AWPCP.run('awpcp/init-recaptcha', ['jquery'],
function($) {
    window['AWPCPreCAPTCHAonLoadCallback'] = function() {
        $( '.awpcp-recaptcha' ).each( function() {
            var element = $( this );

            console.log( element.attr( 'data-sitekey' ) );

            widget = grecaptcha.render( this, {
              'sitekey' : element.attr( 'data-sitekey' ),
              'theme' : 'light'
            } );
        } );
    };
});
