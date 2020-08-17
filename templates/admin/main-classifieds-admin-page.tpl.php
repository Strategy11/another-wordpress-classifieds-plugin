<?php
/**
 * @package AWPCP\Templates
 */

?><?php echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<?php /* translators: %1$s is the opening link tag <a>, %2$s is the closing link tag </a>. */ ?>
<?php $msg = esc_html_x( 'Thank you for using Another WordPress Classifieds Plugin, the #1 WordPress Classifieds Plugin.  Please direct support requests, enhancement ideas and bug reports to the %s.', '... to the <a>AWPCP Support Website link</a>', 'another-wordpress-classifieds-plugin' ); ?>
<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo awpcp_render_success_message( sprintf( $msg, '<a href="https://awpcp.com/get-help/">' . esc_html__( 'AWPCP Support Website', 'another-wordpress-classifieds-plugin' ) . '</a>' ) ); ?>

<?php if ( intval( $hasextrafieldsmodule ) === 1 && intval( $extrafieldsversioncompatibility ) !== 1 ) : ?>
<div id="message" class="awpcp-updated updated fade">
    <p>
        <?php esc_html_e( 'The version of the extra fields module that you are using is not compatible with this version of Another WordPress Classifieds Plugin.', 'another-wordpress-classifieds-plugin' ); ?>
        <a href="https://awpcp.com/contact/"><?php esc_html_e( 'Please request updated Extra Fields module files', 'another-wordpress-classifieds-plugin' ); ?></a>.
    </p>
</div>
<?php endif; ?>

<div class="metabox-holder">
    <div class="meta-box-sortables" <?php echo empty( $sidebar ) ? '' : ' style="float:left;width:70%;"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

        <div class="postbox">
            <h3 class="hndle1"><span><?php esc_html_e( 'Another WordPress Classifieds Plugin Stats', 'another-wordpress-classifieds-plugin' ); ?><span></h3>
            <div class="inside">
                <ul>
                    <li><?php esc_html_e( 'AWPCP version', 'another-wordpress-classifieds-plugin' ); ?>: <strong><?php echo esc_html( $awpcp_db_version ); ?></strong>.</li>

                    <?php $listings_collection = awpcp_listings_collection(); ?>

                    <?php $enabled_listings = $listings_collection->count_enabled_listings(); ?>
                    <li><?php esc_html_e( 'Number of active listings currently in the system', 'another-wordpress-classifieds-plugin' ); ?>: <strong><?php echo intval( $enabled_listings ); ?></strong></li>

                    <?php $disabled_listings = $listings_collection->count_disabled_listings(); ?>
                    <li><?php esc_html_e( 'Number of expired/disabled listings currently in the system', 'another-wordpress-classifieds-plugin' ); ?>: <strong><?php echo intval( $disabled_listings ); ?></strong></li>

                    <?php $invalid_listings = $listings_collection->count_listings() - $enabled_listings - $disabled_listings; ?>
                    <li><?php esc_html_e( 'Number of invalid listings currently in the system', 'another-wordpress-classifieds-plugin' ); ?>: <strong><?php echo intval( $invalid_listings ); ?></strong></li>
                </ul>

                <div style="border-top:1px solid #dddddd;">
                <?php if ( intval( get_awpcp_option( 'freepay' ) ) === 1 ) : ?>
                    <?php if ( adtermsset() ) : ?>
                        <?php /* translators: %s is the link to the Fees admin page. */ ?>
                        <?php $msg = esc_html__( 'You have setup your listing fees. To edit your fees go to %s.', 'another-wordpress-classifieds-plugin' ); ?>
                    <?php else : ?>
                        <?php /* translators: %s is the link to the Fees admin page. */ ?>
                        <?php $msg = esc_html__( 'You have not configured your Listing fees. Go to %s to set up your listing fees. Once that is completed, if you are running in pay mode, the options will automatically appear on the listing form for users to fill out.', 'another-wordpress-classifieds-plugin' ); ?>
                    <?php endif; ?>
                    <?php $url = add_query_arg( 'page', 'awpcp-admin-fees', admin_url( 'admin.php' ) ); ?>
                    <p><?php echo sprintf( $msg, sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'Fees', 'another-wordpress-classifieds-plugin' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                <?php else : ?>
                    <?php /* translators: %s is the link to the Payment Options settigns page. */ ?>
                    <?php $msg = esc_html__( "You currently have your system configured to run in free mode. To change to 'pay' mode go to %s and Check the box labeled 'Charge Listing Fee? (Pay Mode).'", 'another-wordpress-classifieds-plugin' ); ?>
                    <?php
                    $url = add_query_arg(
                        array(
                            'page' => 'awpcp-admin-settings',
                            'g'    => 'payment-settings',
                        ),
                        admin_url( 'admin.php' )
                    );
                    ?>
                    <p><?php echo sprintf( $msg, sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'Payment Options', 'another-wordpress-classifieds-plugin' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                <?php endif; ?>
                </div>

                <?php if ( categoriesexist() ) : ?>

                <div style="border-top:1px solid #dddddd;">
                    <?php /* translators: %s is the link the Manage Categories admin page. */ ?>
                    <?php $msg = esc_html__( 'Go to the %s section to edit/delete current categories or add new categories.', 'another-wordpress-classifieds-plugin' ); ?>
                    <?php $url = awpcp_get_admin_categories_url(); ?>
                    <p><?php echo sprintf( $msg, sprintf( '<a href="%s">%s</a>', $url, esc_html__( 'Manage Categories', 'another-wordpress-classifieds-plugin' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

                    <ul>
                        <li style="margin-bottom:6px;list-style:none;">
                            <?php esc_html_e( 'Total number of categories in the system', 'another-wordpress-classifieds-plugin' ); ?>:
                            <strong><?php echo intval( countcategories() ); ?></strong>
                        </li>

                        <li style="margin-bottom:6px;list-style:none;">
                            <?php esc_html_e( 'Number of Top Level parent categories', 'another-wordpress-classifieds-plugin' ); ?>:
                            <strong><?php echo intval( countcategoriesparents() ); ?></strong>
                        </li>

                        <li style="margin-bottom:6px;list-style:none;">
                            <?php esc_html_e( 'Number of sub level children categories', 'another-wordpress-classifieds-plugin' ); ?>:
                            <strong><?php echo intval( countcategorieschildren() ); ?></strong>
                        </li>
                    </ul>
                </div>

                <?php else : ?>

                <div style="border-top:1px solid #dddddd;">
                    <?php /* translators: %s is the link to the Manage Categories admin page. */ ?>
                    <?php $msg = esc_html__( 'You have not categories defined. Go to the %s section to set up your categories.', 'another-wordpress-classifieds-plugin' ); ?>
                    <?php $url = awpcp_get_admin_categories_url(); ?>
                    <p><?php echo sprintf( $msg, sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'Manage Categories', 'another-wordpress-classifieds-plugin' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                </div>

                <?php endif; ?>

                <?php if ( intval( get_awpcp_option( 'freepay' ) ) === 1 ) : ?>
                <div style="border-top:1px solid #dddddd;">
                    <?php /* translators: %s is the link the Payment Options admin page. */ ?>
                    <?php $msg = esc_html__( "You currently have your system configured to run in pay mode. To change to 'free' mode go to %s and uncheck the box labeled 'Charge Listing Fee? (Pay Mode).'", 'another-wordpress-classifieds-plugin' ); ?>
                    <?php
                    $url = add_query_arg(
                        array(
                            'page' => 'awpcp-admin-settings',
                            'g'    => 'payment-settings',
                        ),
                        admin_url( 'admin.php' )
                    );
                    ?>
                    <p><?php echo sprintf( $msg, sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'Payment Options', 'another-wordpress-classifieds-plugin' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <div class="postbox">
            <div class="inside">
                <?php esc_html_e( 'AWPCP is highly customizable. Use the next button to go to the Settings section to fit AWPCP to your needs.', 'another-wordpress-classifieds-plugin' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=awpcp-admin-settings' ) ); ?>" class="button-primary"><?php esc_html_e( 'Configure AWPCP', 'another-wordpress-classifieds-plugin' ); ?></a>
            </div>
        </div>

    </div>
</div>
