<?php
/**
 * @package AWPCP\Templates
 */

?><p class="awpcp-form-spacer">
    <?php if ( $show_link ) : ?>
    <a href="<?php echo esc_url( $text ); ?>" target="_blank"><?php echo esc_html( _x( 'Read our Terms of Service', 'ad details form', 'another-wordpress-classifieds-plugin' ) ); ?></a>
    <?php else : ?>
    <label><?php echo esc_html( $label ); ?><?php echo $is_required ? '*' : ''; ?></label>
    <textarea class="awpcp-textarea" readonly="readonly" rows="5" cols="50"><?php echo esc_textarea( $text ); ?></textarea>
    <?php endif ?>

    <label class="awpcp-terms-of-service-checkbox awpcp-button">
        <input class="required" id="terms-of-service" type="checkbox" name="<?php esc_attr( $html['name'] ); ?>" value="1" />
        <span><?php echo esc_html( _x( 'I agree to the terms of service', 'ad details form', 'another-wordpress-classifieds-plugin' ) ); ?></span>
    </label>

    <?php echo awpcp_form_error( $html['name'], $errors ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</p>
