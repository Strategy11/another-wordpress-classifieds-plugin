if (typeof jQuery !== 'undefined') {
    (function ($) {
        $.fn.usableform = function() {
            return $(this).each(function() {
                var form = $(this);
                var elements = form.find('[data-usableform]');

                elements.each(function() {
                    var element = $(this);
                    var condition = element.attr('data-usableform').split(':');
                    var target = form.find('[name="' + condition[1] + '"]').not(':hidden');

                    target.change(function() {
                        onTargetChange(target, element, condition);
                    });

                    onTargetChange(target, element, condition);
                });
            });
        }

        function onTargetChange(target, element, condition) {
            if (condition.length == 3 && elementValueMatchesCondition(target, condition[2])) {
                handleElementFulfilledCondition(element, condition[0]);
            } else if (condition.length == 2 && null !== getElementValue(target)) {
                handleElementFulfilledCondition(element, condition[0]);
            } else {
                handleElementUnfulfilledCondition(element, condition[0]);
            }
        }

        function elementValueMatchesCondition(element, condition) {
            var value = getElementValue(element);

            if ($.isArray(value)) {
                return value.indexOf(condition) !== -1;
            } else {
                return value == condition;
            }
        }

        function getElementValue(element) {
            var type = getElementType(element);
            var value;

            if ('checkbox' == type || 'radio' == type) {
                value = element.filter(':checked').val();
            } else {
                value = element.val();
            }

            return value ? value : null;
        }

        function getElementType(element) {
            return element[element.prop ? 'prop' : 'attr']('type');
        }

        function handleElementFulfilledCondition(element, condition) {
            if ('enable-if' == condition) {
                enableElement(element);
            } else if ('show-if' == condition) {
                element.closest('awpcp-admin-form-field').show();
            }
        }

        function enableElement(element) {
            if (element.prop) {
                element.prop('disabled', false);
            } else {
                element.removeAttr('disabled');
            }
        }

        function handleElementUnfulfilledCondition(element, condition) {
            if ('enable-if' == condition) {
                disableElement(element);
            } else if ('show-if' == condition) {
                element.closest('awpcp-admin-form-field').hide();
            }
        }

        function disableElement(element) {
            if (element.prop) {
                element.prop('disabled', true);
            } else {
                element.attr('disabled', 'disabled');
            }
        }
    })(jQuery);
}
