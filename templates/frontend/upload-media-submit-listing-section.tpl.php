<?php
/**
 * @package AWPCP\Templates\Frontend
 */

?><div class="awpcp-order-submit-listing-section awpcp-submit-listing-section">
    <h2 class="awpcp-submit-listing-section-title"><?php echo esc_html_x( 'Upload Files', 'upload media submit listing section', 'another-wordpress-classifieds-plugin' ); ?></h2>

    <div class="awpcp-submit-listing-section-content">
        <div class="awpcp-upload-media-listing-section__loading_mode">
            <?php echo esc_html_x( 'Loading...', 'upload media submit listing section', 'another-wordpress-classifieds-plugin' ); ?>
        </div>
        <div class="awpcp-upload-media-listing-section__edit_mode">
            <?php if ( is_null( $listing ) ) : ?>
            <?php else : ?>

                <?php foreach ( $messages as $message ) : ?>
                    <?php echo awpcp_print_message( $message ); // XSS Ok. ?>
                <?php endforeach; ?>

                <?php include AWPCP_DIR . '/templates/components/media-center.tpl.php'; ?>

            <?php endif; ?>
        </div>
    </div>
</div>
