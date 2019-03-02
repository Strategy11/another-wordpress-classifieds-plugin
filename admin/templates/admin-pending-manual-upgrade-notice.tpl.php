<div class="update-nag awpcp-update-nag clearfix">
    <?php $url = awpcp_get_admin_upgrade_url(); ?>
    <div>
        <span class="awpcp-update-nag-title"><?php echo awpcp_admin_page_title( __( 'Manual Upgrade Required', 'another-wordpress-classifieds-plugin' ) ); ?></span>

        <?php echo str_replace( '<upgrade-link>', sprintf( '<a href="%s">', $url ), $message ); ?>

        <p>
            <?php echo sprintf( '<a class="button button-primary" href="%s">%s</a>', $url, __( 'Upgrade', 'another-wordpress-classifieds-plugin' ) ); ?>
        </p>
    </div>
</div>
