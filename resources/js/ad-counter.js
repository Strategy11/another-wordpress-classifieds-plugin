(function($) {
    $(function() {
        $.post( $.AWPCP.options['ajaxurl'], {
            action: 'awpcp-ad-count-view',
            listing_id: $.AWPCP.get('ad-id'),
        } );
    });
})(jQuery);
