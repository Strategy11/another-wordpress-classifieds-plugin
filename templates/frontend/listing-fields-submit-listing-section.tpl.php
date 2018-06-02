<?php
/**
 * @package AWPCP\Templates\Frontend
 */

?><div class="awpcp-listing-fields-submit-listing-section awpcp-submit-listing-section">
    <h2 class="awpcp-submit-listing-section-title"><?php echo esc_html_x( 'Classified fields', 'listing fields submit listing section', 'another-wordpress-classifieds-plugin' ); ?></h2>
    <div class="awpcp-submit-listing-section-content">
        <div class="awpcp-listing-fields-submit-listing-section__edit_mode">
            <form>
                <?php echo $form_fields; // XSS Ok. ?>

                <p class="form-submit">
                    <input class="awpcp-listing-fields-submit-listing-section--continue-button button button-primary" type="submit" value="<?php echo esc_attr_x( 'Continue', 'listing fields submit listing section', 'another-wordpress-clasifieds-plugin' ); ?>"/>
                </p>
            </form>
        </div>

        <div class="awpcp-listing-fields-submit-listing-section__read_mode">
        </div>
    </div>
</div>

