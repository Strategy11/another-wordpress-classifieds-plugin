<div class="awpcp-admin-sidebar awpcp-postbox-container postbox-container" style="<?php echo $float; ?>">
    <div class="metabox-holder">
        <div class="meta-box-sortables">

            <div class="postbox">
				<?php echo awpcp_html_postbox_handle( array( 'content' => __( 'Like this plugin?', 'another-wordpress-classifieds-plugin' ) ) ); ?>
                <div class="inside">
                    <p><?php _e( 'Why not do any or all of the following:', 'another-wordpress-classifieds-plugin' ); ?></p>
                    <ul>
                        <li class="li_link">
                            <a href="http://wordpress.org/extend/plugins/another-wordpress-classifieds-plugin/">
								<?php _e( 'Give it a good rating on WordPress.org.', 'another-wordpress-classifieds-plugin' ); ?>
                            </a>
                        </li>
                        <li class="li_link">
                            <a href="http://wordpress.org/extend/plugins/another-wordpress-classifieds-plugin/">
								<?php _e( 'Let other people know that it works with your WordPress setup.', 'another-wordpress-classifieds-plugin' ); ?>
                            </a></li>
                        <li class="li_link">
                            <a href="http://www.awpcp.com/premium-modules/?ref=panel"><?php _e( 'Buy a Premium Module', 'another-wordpress-classifieds-plugin' ); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
			<?php if ( count( $modules['premium']['not-installed'] ) != 0 ): ?>
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

					echo awpcp_html_postbox_handle( $params );
					?>

                    <div class="inside">
                        <ul>
							<?php foreach ( $modules['premium']['not-installed'] as $module ): ?>
								<?php if ( ! isset( $module['removed'] ) ) : ?>
                                    <li class="li_link">
                                        <a style="color:#145200;" href="<?php echo $module['url']; ?>" target="_blank">
											<?php echo $module['name']; ?>
                                        </a>
                                    </li>
								<?php endif; ?>
							<?php endforeach; ?>
                        </ul>
                    </div>
                </div>
			<?php endif; ?>

            <div class="postbox">
				<?php echo awpcp_html_postbox_handle( array(
					'content' => __( 'Found a bug?', 'another-wordpress-classifieds-plugin' ) . '&nbsp;' . __( 'Need Support?', 'another-wordpress-classifieds-plugin' )
				) ); ?>
				<?php $tpl = '<a href="%s" target="_blank">%s</a>'; ?>
                <div class="inside">
                    <ul>
						<?php $link = sprintf( $tpl, 'http://www.awpcp.com/quick-start-guide', __( 'Quick Start Guide', 'another-wordpress-classifieds-plugin' ) ); ?>
                        <li><?php echo sprintf( _x( 'Browse the %s.', 'Browse the <a>Quick Start Guide</a>', 'another-wordpress-classifieds-plugin' ), $link ); ?></li>
						<?php $link = sprintf( $tpl, 'http://awpcp.com/docs', __( 'Documentation', 'another-wordpress-classifieds-plugin' ) ); ?>
                        <li><?php echo sprintf( _x( 'Read the full %s.', 'Read the full <a>Documentation</a>', 'another-wordpress-classifieds-plugin' ), $link ); ?></li>
						<?php $link = sprintf( $tpl, 'http://www.awpcp.com/forum', __( 'visit the forums!', 'another-wordpress-classifieds-plugin' ) ); ?>
                        <li><?php echo sprintf( _x( 'Report bugs or get more help: %s.', 'Report bugs or get more help: <a>visit the forums!</a>',
								'another-wordpress-classifieds-plugin' ), $link ); ?></li>
                    </ul>
                </div>
            </div>

            <!-- <div class="postbox">
                <?php echo awpcp_html_postbox_handle( array( 'content' => __( 'Other Modules', 'another-wordpress-classifieds-plugin' ) ) ); ?>

                <div class="inside">

                    <h4><?php _e( "Installed", 'another-wordpress-classifieds-plugin' ); ?><h4>

                    <?php if ( count( $modules['other']['installed'] ) == 0 ): ?>

                    <p><?php __( "No other modules installed", 'another-wordpress-classifieds-plugin' ); ?></p>

                    <?php else: ?>

                    <ul>
                    <?php foreach ( $modules['other']['installed'] as $module ): ?>
                        <li><?php echo $module['name']; ?></li>
                    <?php endforeach; ?>
                    </ul>

                    <?php endif; ?>


                    <h4><?php _e( "Not Installed", 'another-wordpress-classifieds-plugin' ); ?><h4>

                    <?php if ( count( $modules['other']['not-installed'] ) == 0 ): ?>

                    <p><?php __( "All other modules installed!", 'another-wordpress-classifieds-plugin' ); ?></p>

                    <?php else: ?>

                    <ul>
                    <?php foreach ( $modules['other']['not-installed'] as $module ): ?>
                        <li><a href="<?php echo $module['url']; ?>"><?php echo $module['name']; ?></a></li>
                    <?php endforeach; ?>
                    </ul>

                    <?php endif; ?>

                </div>
            </div> -->

        </div>
    </div>
</div>
