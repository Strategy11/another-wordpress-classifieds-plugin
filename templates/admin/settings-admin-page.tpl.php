<?php
/**
 * Template for the Settings admin page.
 *
 * @package AWPCP\Admin\Pages
 */

awpcp_print_messages(); ?>

			<h2 class="nav-tab-wrapper">
			<?php foreach ($groups as $g): ?>
				<?php $href = add_query_arg(array('g' => $g->slug), awpcp_current_url()); ?>
				<?php $active = $group->slug == $g->slug ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>
				<a href="<?php echo esc_url( $href ); ?>" class="<?php echo esc_attr( $active ) ?>"><?php echo $g->name ?></a>
			<?php endforeach ?>
			</h2>

			<?php do_action('awpcp-admin-settings-page--' . $group->slug); ?>

			<form class="settings-form" action="<?php echo admin_url('options.php') ?>" method="post">
				<?php settings_fields( $settings->setting_name ); ?>
				<input type="hidden" name="group" value="<?php echo $group->slug ?>" />

				<?php $settings->load() ?>
				<?php
				ob_start();
				do_settings_sections($group->slug);
				$output = ob_get_contents();
				ob_end_clean();
				?>

				<?php if ( $output ): ?>
				<p class="submit hidden">
					<input type="submit" value="<?php _e( 'Save Changes', 'another-wordpress-classifieds-plugin' ); ?>" class="button-primary" id="submit-top" name="submit">
				</p>
				<?php endif; ?>

				<?php
					// A hidden submit button is necessary so that whenever the user hits enter on an input field,
					// that one is the button that is triggered, avoiding other submit buttons in the form to trigger
					// unwanted behaviours.
				?>

				<?php echo $output; ?>

				<?php if ( $output ): ?>
				<p class="submit">
					<input type="submit" value="<?php _e( 'Save Changes', 'another-wordpress-classifieds-plugin' ) ?>" class="button-primary" id="submit-bottom" name="submit">
				</p>
				<?php endif; ?>
			</form>

            <hr/>

            <?php
                $heading_params = array(
                    'content' => esc_html__( 'Import and Export Settings', 'another-wordpress-classifieds-plugin' ),
                );

                echo awpcp_html_admin_second_level_heading( $heading_params ); // WPCS: XSS Ok.
            ?>

            <ul>
                <li>
                    <a href="<?php echo esc_url( $export_settings_url ); ?>"><?php echo esc_html__( 'Export Settings', 'another-wordpress-classifieds-plugin' ); ?></a>
                    <br/><span><?php echo esc_html__( 'Download a JSON file with the values for all settings.', 'another-wordpress-classifieds-plugin' ); ?></span>
                </li>
                <li>
                    <a href="<?php echo esc_url( $import_settings_url ); ?>"><?php echo esc_html__( 'Import Settings', 'another-wordpress-classifieds-plugin' ); ?></a>
                    <br/><span><?php echo esc_html__( 'Update the plugin settings from a previously generated JSON file.', 'another-wordpress-classifieds-plugin' ); ?></span>
                </li>
            </ul>
