/*global ajaxurl:true */

if (jQuery !== undefined) {
    (function($, undefined) {

        /* handlers for Fees page */
        $(function() {
            var panel = $('#awpcp-admin-fees');

            panel.admin({
                actions: {
                    add: 'awpcp-fees-add',
                    remove: 'awpcp-fees-delete'
                },
                ajaxurl: ajaxurl,
                base: '#fee-',
                include: ['add', 'trash'],

                onFormReady: function () {
                    $('.awpcp-fees .category-checklist').each(function() {
                        $.noop(new $.AWPCP.CategoriesChecklist(this));
                    });
                }
            });
        });

        $();

    })(jQuery);
}
