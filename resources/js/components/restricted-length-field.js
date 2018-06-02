/*global AWPCP*/
AWPCP.run( 'awpcp/restricted-length-field', [
    'jquery',
], function( $ ) {
    var RestrictedLengthField = function(element) {
        var self = this;

        self.element = $(element);
        self.container = self.element.closest('.awpcp-form-spacer');
        self.placeholder = self.container.find('.characters-left-placeholder');

        self.allowed = parseInt(self.element.attr('data-max-characters'), 10);
        self.remaining = parseInt(self.element.attr('data-remaining-characters'), 10);

        self.element.bind('keyup keydown', function() {
            var text = self.element.val();
            if (self.allowed > 0) {
                if (text.length > self.allowed) {
                    text = text.substring(0, self.allowed);
                    self.element.val(text);
                }

                self.placeholder.text(self.allowed - text.length);
            }
        }).trigger('keyup');
    };

    return RestrictedLengthField;
} );
