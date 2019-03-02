<?php
/**
 * @package AWPCP/Admin/Templates
 */

?>
<div class="awpcp-admin-sidebar awpcp-postbox-container postbox-container" style="<?php echo esc_attr( $float ); ?>">
    <div class="metabox-holder">
        <div class="meta-box-sortables">

            <div class="postbox">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput
				echo awpcp_html_postbox_handle( array( 'content' => __( 'Like this plugin?', 'another-wordpress-classifieds-plugin' ) ) );
				?>
                <div class="inside">
                    <p><?php esc_html_e( 'Why not do any or all of the following:', 'another-wordpress-classifieds-plugin' ); ?></p>
                    <ul>
                        <li class="li_link">
                            <a href="http://wordpress.org/extend/plugins/another-wordpress-classifieds-plugin/">
								<?php esc_html_e( 'Give it a good rating on WordPress.org.', 'another-wordpress-classifieds-plugin' ); ?>
                            </a>
                        </li>
                        <li class="li_link">
                            <a href="http://wordpress.org/extend/plugins/another-wordpress-classifieds-plugin/">
								<?php esc_html_e( 'Let other people know that it works with your WordPress setup.', 'another-wordpress-classifieds-plugin' ); ?>
                            </a></li>
                        <li class="li_link">
                            <a href="http://www.awpcp.com/premium-modules/?ref=panel"><?php esc_html_e( 'Buy a Premium Module', 'another-wordpress-classifieds-plugin' ); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
			<?php if ( count( $modules['premium']['not-installed'] ) !== 0 ) : ?>
                <div class="awpcp-get-a-premium-module postbox" style="background-color:#FFFFCF; border-color:#0EAD00; border-width:3px;">
					<?php
					$params = array(
						'heading_attributes' => array(
							'style' => 'color:#145200',
						),
						'span_attributes'    => array(
							'class' => 'red',
						),
						'content'            => '<strong>' . __( 'Get a Premium Module!', 'another-wordpress-classifieds-plugin' ) . '</strong>',
					);
					// phpcs:ignore WordPress.Security.EscapeOutput
					echo awpcp_html_postbox_handle( $params );
					?>

                    <div class="inside">
                        <ul>
							<?php foreach ( $modules['premium']['not-installed'] as $module ) : ?>
								<?php if ( ! isset( $module['removed'] ) ) : ?>
                                    <li class="li_link">
                                        <a style="color:#145200;" href="<?php echo esc_url( $module['url'] ); ?>" target="_blank">
											<?php echo esc_html( $module['name'] ); ?>
                                        </a>
                                    </li>
								<?php endif; ?>
							<?php endforeach; ?>
                        </ul>
                    </div>
                </div>
			<?php endif; ?>

            <div class="postbox">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput
				echo awpcp_html_postbox_handle(
					array(
						'content' => __( 'Found a bug?', 'another-wordpress-classifieds-plugin' ) . '&nbsp;' . __( 'Need Support?', 'another-wordpress-classifieds-plugin' ),
					)
				);
				?>
				<?php $tpl = '<a href="%s" target="_blank">%s</a>'; ?>
                <div class="inside">
                    <ul>

						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput
						$atag = sprintf( $tpl, 'http://www.awpcp.com/quick-start-guide', __( 'Quick Start Guide', 'another-wordpress-classifieds-plugin' ) );
						?>
                        <li>
							<?php
							// phpcs:ignore
							echo sprintf( esc_html_x( 'Browse the %s.', 'Browse the <a>Quick Start Guide</a>', 'another-wordpress-classifieds-plugin' ), $atag );
							?>
                        </li>
						<?php /* translators: %s: Link */ ?>
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput
						$atag = sprintf( $tpl, 'http://awpcp.com/docs', __( 'Documentation', 'another-wordpress-classifieds-plugin' ) );
						?>
						<?php /* translators: %s: Documentation */ ?>
                        <li>
                            <?php
                            // phpcs:ignore
                            echo sprintf( esc_html_x( 'Read the full %s.', 'Read the full <a>Documentation</a>', 'another-wordpress-classifieds-plugin' ), $atag );
                            ?>
                        </li>
						<?php /* translators: %s: Forums*/ ?>
						<?php $atag = sprintf( $tpl, 'http://www.awpcp.com/forum', __( 'visit the forums!', 'another-wordpress-classifieds-plugin' ) ); ?>
                        <li>
							<?php
							// phpcs:ignore
							echo sprintf( esc_html_x( 'Report bugs or get more help: %s.', 'Report bugs or get more help: <a>visit the forums!</a>', 'another-wordpress-classifieds-plugin' ), $atag );
							?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
