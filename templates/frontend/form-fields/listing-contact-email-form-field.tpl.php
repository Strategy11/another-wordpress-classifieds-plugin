<?php
/**
 * @package AWPCP\Templates\FormFields
 */

?><p class="awpcp-form-field awpcp-clearfix   awpcp-form-spacer">
    <label class="awpcp-form-field__label" for="<?php echo esc_attr( $html['id'] ); ?>"><?php echo esc_html( $label ); ?><?php echo $required ? '*' : ''; ?></label>
    <input class="awpcp-textfield inputbox <?php echo esc_attr( $validators ); ?>" id="<?php echo esc_attr( $html['id'] ); ?>" <?php echo $html['readonly'] ? 'readonly="readonly"' : ''; ?> type="text" size="50" name="<?php echo esc_attr( $html['name'] ); ?>" value="<?php echo awpcp_esc_attr( $value ); // XSS Okay. ?>" />
    <?php if ( ! empty( $help_text ) ) : ?>
    <label class="helptext" for="<?php echo esc_attr( $html['id'] ); ?>"><?php echo $help_text; // XSS Okay. ?></label>
    <?php endif; ?>
    <?php echo awpcp_form_error( $html['name'], $errors ); // XSS Okay. ?>
</p>
