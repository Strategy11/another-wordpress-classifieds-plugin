<?php
/**
 * @package AWPCP\Templates\Frontend
 */

?><div class="awpcp-save-submit-listing-section awpcp-submit-listing-section">
    <h2 class="awpcp-submit-listing-section-title"><?php echo esc_html( $section_label ); ?></h2>
    <div class="awpcp-submit-listing-section-content">
        <div class="awpcp-save-submit-listing-section__edit_mode">
            <p class="form-submit">
                <input class="button" type="reset" value="<?php echo esc_attr( _x( 'Clear form', 'save submit listing section', 'another-wordpress-classifieds-plugin' ) ); ?>"/>
                <input class="button button-primary" type="submit" value="<?php echo esc_attr( $button_label ); ?>" name="submit"/>
            </p>
        </div>
    </div>
</div>
