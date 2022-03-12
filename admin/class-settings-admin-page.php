<?php
/**
 * @package AWPCP\Admin\Pages
 */

/**
 * Constructor function for AWPCP_SettingsAdminPage
 */
function awpcp_settings_admin_page() {
    return new AWPCP_SettingsAdminPage(
        awpcp()->container['SettingsManager'],
        awpcp()->container['Settings'],
        awpcp()->container['Request']
    );
}

/**
 * Admin page that allows administrators to configure the plugin.
 *
 * @SuppressWarnings(PHPMD)
 */
class AWPCP_SettingsAdminPage {

    /**
     * @var object
     */
	private $settings;

    /**
     * @var object
     */
	private $request;

    /**
     * Constructor.
     *
     * @param object $settings_manager  An instance of SettingsManager.
     * @param object $settings          An instance of SettingsAPI.
     * @param object $request           An instance of Request.
     */
	public function __construct( $settings_manager, $settings, $request ) {
        $this->settings_manager = $settings_manager;
        $this->settings         = $settings;
        $this->request          = $request;

		$this->instantiate_auxiliar_pages();
	}

    /**
     * Enqueue page scripts.
     */
	public function enqueue_scripts() {
		wp_enqueue_script( 'awpcp-admin-settings' );
	}

    /**
     * Renders the page.
     */
	public function dispatch() {
        $groups         = $this->settings_manager->get_settings_groups( true );
        $subgroups      = $this->settings_manager->get_settings_subgroups();
        $current_groups = $this->get_current_groups( $groups, $subgroups );

		unset( $groups['private-settings'] );

		$params = array(
            'groups'           => $groups,
            'subgroups'        => $subgroups,
            'current_group'    => $current_groups['group'],
            'current_subgroup' => $current_groups['subgroup'],
            'settings'         => $this->settings,
            'setting_name'     => $this->settings->setting_name,
            'current_url'      => remove_query_arg( [ 'sg', 'g' ], awpcp_current_url() ),
		);

		$template = AWPCP_DIR . '/templates/admin/settings-admin-page.tpl.php';

		return awpcp_render_template( $template, $params );
	}

    /**
     * @since 4.0.0
     */
    private function get_current_groups( $groups, $subgroups ) {
        $subgroup_id = $this->request->param( 'sg' );

        if ( isset( $subgroups[ $subgroup_id ] ) ) {
            $subgroup = $subgroups[ $subgroup_id ];
            $group    = $this->sort_group_subgroups( $groups[ $subgroup['parent'] ], $subgroups );

            return compact( 'group', 'subgroup' );
        }

        $group_id = $this->request->param( 'g' );

        if ( empty( $groups[ $group_id ]['subgroups'] ) ) {
            $group_id = 'general-settings';
        }

        $group       = $this->sort_group_subgroups( $groups[ $group_id ], $subgroups );
        $subgroup_id = reset( $group['subgroups'] );
        $subgroup    = $subgroups[ $subgroup_id ];

        return compact( 'group', 'subgroup' );
    }

    /**
     * @since 4.0.0
     */
    private function sort_group_subgroups( $group, $subgroups ) {
        $group['subgroups'] = array_intersect( array_keys( $subgroups ), $group['subgroups'] );

        return $group;
    }

	private function instantiate_auxiliar_pages() {
		$pages = awpcp_classfieds_pages_settings();
	}
}

function awpcp_classfieds_pages_settings() {
	return new AWPCP_Classified_Pages_Settings( awpcp_missing_pages_finder() );
}

class AWPCP_Classified_Pages_Settings {

	private $missing_pages_finder;

	public function __construct( $missing_pages_finder ) {
		$this->missing_pages_finder = $missing_pages_finder;

		add_action('awpcp-admin-settings-page--pages-settings', array($this, 'dispatch'));
	}

	public function dispatch() {
		global $awpcp;

		if ( $this->should_restore_pages() ) {
			$restored_pages = awpcp_pages_creator()->restore_missing_pages();
		} else {
			$restored_pages = array();
		}

		$missing = awpcp_array_filter_recursive( $this->missing_pages_finder->find_broken_page_id_references() );

		ob_start();
			include(AWPCP_DIR . '/admin/templates/admin-panel-settings-pages-settings.tpl.php');
			$content = ob_get_contents();
		ob_end_clean();

		echo $content;
	}

	private function should_restore_pages() {
		$nonce = awpcp_post_param( '_wpnonce' );
		$restore = awpcp_post_param( 'restore-pages', false );

		return $restore && wp_verify_nonce( $nonce, 'awpcp-restore-pages' );
	}
}

class AWPCP_Facebook_Page_Settings {

	public function maybe_redirect() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
	}

	public function get_current_action() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
	}

	public function dispatch() {
		_deprecated_function( __FUNCTION__, '4.1.8' );
	}
}
