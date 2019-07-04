<?php echo $message; ?>

<?php /* translators: %1$s is the opening link tag <a>, %2$s is the closing link tag </a>. */ ?>
<?php $msg = esc_html_x( 'Thank you for using Another WordPress Classifieds Plugin, the #1 WordPress Classifieds Plugin.  Please direct support requests, enhancement ideas and bug reports to the %s.', '... to the <a>AWPCP Support Website link</a>', 'another-wordpress-classifieds-plugin' ); ?>
<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo awpcp_render_success_message( sprintf( $msg, '<a href="http://www.awpcp.com/forum/">' . esc_html__( 'AWPCP Support Website', 'another-wordpress-classifieds-plugin' ) . '</a>' ) ); ?>

<?php if ( $hasextrafieldsmodule == 1 && ! ( $extrafieldsversioncompatibility == 1 ) ) : ?>
<div id="message" class="awpcp-updated updated fade">
    <p>
        <?php _e( 'The version of the extra fields module that you are using is not compatible with this version of Another WordPress Classifieds Plugin.', 'another-wordpress-classifieds-plugin' ); ?>
        <a href="http://www.awpcp.com/contact/"><?php _e( 'Please request updated Extra Fields module files', 'another-wordpress-classifieds-plugin' ); ?></a>.
    </p>
</div>
<?php endif; ?>

<div class="metabox-holder">
    <div class="meta-box-sortables" <?php echo empty( $sidebar ) ? '' : ' style="float:left;width:70%;"'; ?>>

        <div class="postbox">
            <h3 class="hndle1"><span><?php _e( 'Another WordPress Classifieds Plugin Stats', 'another-wordpress-classifieds-plugin' ); ?><span></h3>
            <div class="inside">
                <ul>
                    <li><?php _e( 'AWPCP version', 'another-wordpress-classifieds-plugin' ); ?>: <strong><?php echo $awpcp_db_version; ?></strong>.</li>

                    <?php $listings_collection = awpcp_listings_collection(); ?>

                    <?php $enabled_listings = $listings_collection->count_enabled_listings(); ?>
                    <li><?php _e( 'Number of active listings currently in the system', 'another-wordpress-classifieds-plugin' ); ?>: <strong><?php echo $enabled_listings; ?></strong></li>

                    <?php $disabled_listings = $listings_collection->count_disabled_listings(); ?>
                    <li><?php _e( 'Number of expired/disabled listings currently in the system', 'another-wordpress-classifieds-plugin' ); ?>: <strong><?php echo $disabled_listings; ?></strong></li>

                    <?php $invalid_listings = $listings_collection->count_listings() - $enabled_listings - $disabled_listings; ?>
                    <li><?php _e( 'Number of invalid listings currently in the system', 'another-wordpress-classifieds-plugin' ); ?>: <strong><?php echo $invalid_listings; ?></strong></li>
                </ul>

                <div style="border-top:1px solid #dddddd;">
                <?php if ( get_awpcp_option( 'freepay' ) == 1 ) : ?>
                    <?php if ( adtermsset() ) : ?>
                        <?php $msg = __( 'You have setup your listing fees. To edit your fees go to %s.', 'another-wordpress-classifieds-plugin' ); ?>
                    <?php else : ?>
                        <?php $msg = __( 'You have not configured your Listing fees. Go to %s to set up your listing fees. Once that is completed, if you are running in pay mode, the options will automatically appear on the listing form for users to fill out.', 'another-wordpress-classifieds-plugin' ); ?>
                    <?php endif; ?>
                    <?php $url = add_query_arg( 'page', 'awpcp-admin-fees', admin_url( 'admin.php' ) ); ?>
                    <p><?php echo sprintf( $msg, sprintf( '<a href="%s">%s</a>', esc_url( $url ), __( 'Fees', 'another-wordpress-classifieds-plugin' ) ) ); ?></p>
                <?php else : ?>
                    <?php $msg = __( "You currently have your system configured to run in free mode. To change to 'pay' mode go to %s and Check the box labeled 'Charge Listing Fee? (Pay Mode).'", 'another-wordpress-classifieds-plugin' ); ?>
                    <?php
                    $url = add_query_arg(
                        [
                            'page' => 'awpcp-admin-settings',
                            'g'    => 'payment-settings',
                        ],
                        admin_url( 'admin.php' )
                    );
                    ?>
                    <p><?php echo sprintf( $msg, sprintf( '<a href="%s">%s</a>', esc_url( $url ), __( 'Payment Options', 'another-wordpress-classifieds-plugin' ) ) ); ?></p>
                <?php endif; ?>
                </div>

                <?php if ( categoriesexist() ) : ?>

                <div style="border-top:1px solid #dddddd;">
                    <?php $msg = __( 'Go to the %s section to edit/delete current categories or add new categories.', 'another-wordpress-classifieds-plugin' ); ?>
                    <?php $url = awpcp_get_admin_categories_url(); ?>
                    <p><?php echo sprintf( $msg, sprintf( '<a href="%s">%s</a>', $url, __( 'Manage Categories', 'another-wordpress-classifieds-plugin' ) ) ); ?></p>

                    <ul>
                        <?php $totalcategories = countcategories(); ?>
                        <li style="margin-bottom:6px;list-style:none;">
                            <?php _e( 'Total number of categories in the system', 'another-wordpress-classifieds-plugin' ); ?>:
                            <strong><?php echo $totalcategories; ?></strong>
                        </li>

                        <?php $totalparentcategories = countcategoriesparents(); ?>
                        <li style="margin-bottom:6px;list-style:none;">
                            <?php _e( 'Number of Top Level parent categories', 'another-wordpress-classifieds-plugin' ); ?>:
                            <strong><?php echo $totalparentcategories; ?></strong>
                        </li>

                        <?php $totalchildrencategories = countcategorieschildren(); ?>
                        <li style="margin-bottom:6px;list-style:none;">
                            <?php _e( 'Number of sub level children categories', 'another-wordpress-classifieds-plugin' ); ?>:
                            <strong><?php echo $totalchildrencategories; ?></strong>
                        </li>
                    </ul>
                </div>

                <?php else : ?>

                <div style="border-top:1px solid #dddddd;">
                    <?php $msg = __( 'You have not categories defined. Go to the %s section to set up your categories.', 'another-wordpress-classifieds-plugin' ); ?>
                    <?php $url = awpcp_get_admin_categories_url(); ?>
                    <p><?php echo sprintf( $msg, sprintf( '<a href="%s">%s</a>', esc_url( $url ), __( 'Manage Categories', 'another-wordpress-classifieds-plugin' ) ) ); ?></p>
                </div>

                <?php endif; ?>

                <?php if ( get_awpcp_option( 'freepay' ) == 1 ) : ?>
                <div style="border-top:1px solid #dddddd;">
                    <?php $msg = __( "You currently have your system configured to run in pay mode. To change to 'free' mode go to %s and uncheck the box labeled 'Charge Listing Fee? (Pay Mode).'", 'another-wordpress-classifieds-plugin' ); ?>
                    <?php
                    $url = add_query_arg(
                        [
                            'page' => 'awpcp-admin-settings',
                            'g'    => 'payment-settings',
                        ],
                        admin_url( 'admin.php' )
                    );
                    ?>
                    <p><?php echo sprintf( $msg, sprintf( '<a href="%s">%s</a>', esc_url( $url ), __( 'Payment Options', 'another-wordpress-classifieds-plugin' ) ) ); ?></p>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <div class="postbox">
            <div class="inside">
                <?php $href = admin_url( 'admin.php?page=awpcp-admin-settings' ); ?>
                <?php _e( 'AWPCP is highly customizable. Use the next button to go to the Settings section to fit AWPCP to your needs.', 'another-wordpress-classifieds-plugin' ); ?>
                <a href="<?php echo esc_url( $href ); ?>" class="button-primary"><?php _e( 'Configure AWPCP', 'another-wordpress-classifieds-plugin' ); ?></a>
            </div>
        </div>

    </div>
</div>
