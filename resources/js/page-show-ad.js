/*global confirm, alert*/
(function($, undefined) {

    var AWPCP = jQuery.AWPCP = jQuery.extend({}, jQuery.AWPCP, AWPCP);

    $.AWPCP.FlagLink = function(link) {
        var self = this;

        self.id = parseInt($(link).attr('data-ad'), 10);

        self.link = link.click(function(event) {
            event.preventDefault();
            var proceed = confirm($.AWPCP.l10n('page-show-ad', 'flag-confirmation-message'));
            if (proceed) {
                self.flag_ad();
            }
        });
    };

    $.extend($.AWPCP.FlagLink.prototype, {
        flag_ad: function() {
            var self = this;

            $.ajax({
                url: $.AWPCP.get('ajaxurl'),
                data: {
                    'action': 'awpcp-flag-ad',
                    'ad': self.id,
                    'nonce': $.AWPCP.get('page-show-ad-flag-ad-nonce')
                },
                success: $.proxy(self.callback, self),
                error: $.proxy(self.callback, self)
            });
        },

        callback: function(data) {
            if (parseInt(data, 10) === 1) {
                alert($.AWPCP.l10n('page-show-ad', 'flag-success-message'));
            } else {
                alert($.AWPCP.l10n('page-show-ad', 'flag-error-message'));
            }
        }
    });

    $(function() {
        $.noop( new $.AWPCP.FlagLink( $( '.awpcp-flag-listing-link' ) ) );
    });

    $(function() {
        if ( typeof $.fn.lightGallery === 'undefined' ) {
            return;
        }

        $( 'body' ).on( 'click', '.awpcp-listing-primary-image-thickbox-link, .thickbox, .awpcp-listing-primary-image-listing-link:not(.adhasnoimage)', function( event ) {
            event.preventDefault();

            var $link = $( this );
            var currentGalleryItem = 0;
            var galleryItems = null;

            // Single ads.
            if ($link.parents('#showawpcpadpage').length > 0) {
                galleryItems = $link.closest('#showawpcpadpage').find('.awpcp-listing-primary-image-thickbox-link').data('gallery-images');
            }

            // Browse ads.
            if ($link.parents('.awpcp-listing-excerpt').length > 0) {
                galleryItems = $link.data('gallery-images');
            }



            if (typeof galleryItems !== 'undefined') {
                for (var i = galleryItems.length - 1; i >= 0; i = i - 1) {
                    if (galleryItems[i].src === $link.attr('href')) {
                        currentGalleryItem = i;
                    }
                }

                $link.lightGallery({
                    download: false,
                    dynamic: true,
                    dynamicEl: galleryItems,
                    index: currentGalleryItem
                });
            }

            return false;
        } );

    });
})(jQuery);
