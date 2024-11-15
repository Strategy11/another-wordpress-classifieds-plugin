<?php
/**
 * Onboarding Wizard - Success (You're All Set!) Step.
 *
 * @package Tasty_Recipes
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( 'You are not allowed to call this page directly.' );
}

?>
<section id="awpcp-onboarding-success-step" class="awpcp-onboarding-step awpcp-card-box hidden" data-step-name="<?php echo esc_attr( $step ); ?>">
    <div class="awpcp-card-box-header">
        <?php // awpcp_inline_svg( 'icon-check.svg' ); ?>
    </div>

    <div class="awpcp-card-box-content">
        <h2 class="awpcp-card-box-title"><?php esc_html_e( 'You\'re All Set!', 'another-wordpress-classifieds-plugin' ); ?></h2>
        <p class="awpcp-card-box-text">
            <?php esc_html_e( 'Congratulations on completing the onboarding process! We hope you enjoy using Tasty Recipes Lite Plugin.', 'another-wordpress-classifieds-plugin' ); ?>
        </p>
    </div>

    <div class="awpcp-card-box-footer">
        <a href="<?php echo '#'; ?>" class="tasty-button tasty-highlight">
            <?php esc_html_e( 'Go to Dashboard', 'another-wordpress-classifieds-plugin' ); ?>
        </a>

        <a href="<?php echo '#'; ?>" class="tasty-button tasty-button-pink">
            <?php esc_html_e( 'Go to Settings', 'another-wordpress-classifieds-plugin' ); ?>
        </a>
    </div>
</section>
