<?php
/**
 * Template for the Settings admin page.
 *
 * @package AWPCP\Admin\Pages
 */

?><?php settings_errors(); ?>

			<h2 class="nav-tab-wrapper">
			<?php foreach ( $groups as $group ) : ?>
                <?php if ( count( $group['subgroups'] ) ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'g', $group['id'], $current_url ) ); ?>" class="<?php echo esc_attr( $group['id'] === $current_group['id'] ? 'nav-tab nav-tab-active' : 'nav-tab' ); ?>"><?php echo esc_html( $group['name'] ); ?></a>
                <?php endif; ?>
			<?php endforeach; ?>
			</h2>

            <?php if ( count( $current_group['subgroups'] ) > 1 ) : ?>
            <ul class="awpcp-settings-sub-groups">
            <?php foreach ( $current_group['subgroups'] as $subgroup_id ) : ?>
                <li class="<?php echo esc_attr( $current_subgroup['id'] === $subgroup_id ? 'awpcp-current' : '' ); ?>">
                    <a href="<?php echo esc_url( add_query_arg( 'sg', $subgroup_id, $current_url ) ); ?>"><?php echo esc_html( $subgroups[ $subgroup_id ]['name'] ); ?></a>
                </li>
            <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php
                // TODO: DO we still need this?
                do_action( 'awpcp-admin-settings-page--' . $current_group['id'] ); // phpcs:disable WordPress.NamingConventions.ValidHookName.UseUnderscores
            ?>

			<form class="settings-form" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">

				<?php settings_fields( $setting_name ); ?>

				<input type="hidden" name="group" value="<?php echo esc_attr( $current_group['id'] ); ?>" />
				<input type="hidden" name="subgroup" value="<?php echo esc_attr( $current_subgroup['id'] ); ?>" />

				<?php $settings->load(); ?>
				<?php
				ob_start();
				do_settings_sections( $current_subgroup['id'] );
				$output = ob_get_contents();
				ob_end_clean();
				?>

				<?php if ( $output ) : ?>
				<p class="submit hidden">
					<input type="submit" value="<?php esc_attr_e( 'Save Changes', 'another-wordpress-classifieds-plugin' ); ?>" class="button-primary" id="submit-top" name="submit">
				</p>
				<?php endif; ?>

				<?php
					// A hidden submit button is necessary so that whenever the user hits enter on an input field,
					// that one is the button that is triggered, avoiding other submit buttons in the form to trigger
					// unwanted behaviours.
				?>

				<?php echo $output; // XSS Ok. ?>

				<?php if ( $output ) : ?>
				<p class="submit">
					<input type="submit" value="<?php esc_attr_e( 'Save Changes', 'another-wordpress-classifieds-plugin' ); ?>" class="button-primary" id="submit-bottom" name="submit">
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
