<?php
/**
 * Template used to build the content of the admin notice shown when the plugin
 * has pending manual upgrade tasks.
 *
 * @package AWPCP\Templates\Admin
 */

?><div class="update-nag awpcp-update-nag clearfix">
    <?php $url = awpcp_get_admin_upgrade_url(); ?>
    <div>
        <span class="awpcp-update-nag-title"><?php echo awpcp_admin_page_title( esc_html__( 'Manual Upgrade Required', 'another-wordpress-classifieds-plugin' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>

        <?php echo str_replace( '<upgrade-link>', sprintf( '<a href="%s">', esc_url( $url ) ), $message ); ?>

        <p>
            <?php printf( '<a class="button button-primary" href="%s">%s</a>', esc_url( $url ), esc_html__( 'Upgrade', 'another-wordpress-classifieds-plugin' ) ); ?>
        </p>
    </div>
</div>
