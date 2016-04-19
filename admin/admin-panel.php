<?php
/**
 * AWPCP Classifieds Management Panel functions
 */

// require_once(AWPCP_DIR . '/admin/admin-panel-upgrade.php');
// require_once(AWPCP_DIR . '/admin/admin-panel-csv-importer.php');
// require_once(AWPCP_DIR . '/admin/admin-panel-debug.php');
// require_once(AWPCP_DIR . '/admin/admin-panel-categories.php');
// require_once(AWPCP_DIR . '/admin/admin-panel-fees.php');
// require_once(AWPCP_DIR . '/admin/admin-panel-credit-plans.php');
// require_once(AWPCP_DIR . '/admin/admin-panel-listings.php');
// require_once(AWPCP_DIR . '/admin/admin-panel-uninstall.php');
require_once(AWPCP_DIR . '/admin/admin-panel-users.php');

function awpcp_admin_panel() {
    return new AWPCP_Admin( awpcp_manual_upgrade_tasks_manager() );
}

class AWPCP_Admin {

    private $upgrade_tasks;

	public function __construct( $upgrade_tasks ) {
        $this->upgrade_tasks = $upgrade_tasks;

		$this->title = awpcp_admin_page_title();
		$this->menu = _x('Classifieds', 'awpcp admin menu', 'another-wordpress-classifieds-plugin');

		// not a page, but an extension to the Users table
		$this->users = new AWPCP_AdminUsers();

		// $this->upgrade = new AWPCP_AdminUpgrade(false, false, $this->menu);
		// $this->settings = new AWPCP_Admin_Settings();
		// $this->credit_plans = new AWPCP_AdminCreditPlans();
		// $this->categories = new AWPCP_AdminCategories();
		// $this->fees = new AWPCP_AdminFees();
		// $this->listings = new AWPCP_Admin_Listings();
		// $this->importer = new AWPCP_Admin_CSV_Importer();
		// $this->debug = new AWPCP_Admin_Debug();
		// $this->uninstall = new AWPCP_Admin_Uninstall();

		add_action('wp_ajax_disable-quick-start-guide-notice', array($this, 'disable_quick_start_guide_notice'));
		add_action('wp_ajax_disable-widget-modification-notice', array($this, 'disable_widget_modification_notice'));

		add_action('admin_init', array($this, 'init'));
		add_action('admin_enqueue_scripts', array($this, 'scripts'));
		add_action('admin_menu', array($this, 'menu'));

        $admin_menu_builder = new AWPCP_AdminMenuBuilder( awpcp()->router );
        add_action( 'admin_menu', array( $admin_menu_builder, 'build_menu' ) );

		add_action('admin_notices', array($this, 'notices'));
		add_action( 'awpcp-admin-notices', array( $this, 'check_duplicate_page_names' ) );

		// make sure AWPCP admins (WP Administrators and/or Editors) can edit settings
		add_filter('option_page_capability_awpcp-options', 'awpcp_admin_capability');

		// hook filter to output Admin panel sidebar. To remove the sidebar
		// just remove this action
		add_filter('awpcp-admin-sidebar', 'awpcp_admin_sidebar_output', 10, 2);
	}

	public function configure_routes( $router ) {
        if ( $this->upgrade_tasks->has_pending_tasks( 'plugin' ) ) {
            $this->configure_manual_upgrade_routes( 'awpcp-admin-upgrade', $router );
        } else {
            $this->configure_regular_routes( 'awpcp.php', $router );
        }
    }

    private function configure_manual_upgrade_routes( $parent_menu, $router ) {
        $parent_page = $this->add_main_classifieds_admin_page(
            $parent_menu,
            'awpcp_manual_upgrade_admin_page',
            $router
        );

        $this->add_manual_upgrade_admin_page( $parent_page, __( 'Classifieds', 'AWPCP' ), $parent_menu, $router );
    }

    private function add_main_classifieds_admin_page( $parent_menu, $handler_constructor, $router ) {
        return $router->add_admin_page(
            __( 'Classifieds', 'AWPCP' ),
            awpcp_admin_page_title( __( 'AWPCP', 'AWPCP' ) ),
            $parent_menu,
            $handler_constructor,
            awpcp_admin_capability(),
            MENUICO
        );
    }

    private function add_manual_upgrade_admin_page( $parent_page, $menu_title, $menu_slug, $router ) {
        $router->add_admin_subpage(
            $parent_page,
            $menu_title,
            awpcp_admin_page_title( __( 'Manual Upgrade', 'AWPCP' ) ),
            $menu_slug,
            'awpcp_manual_upgrade_admin_page',
            awpcp_admin_capability(),
            0
        );
    }

    private function configure_regular_routes( $parent_menu, $router ) {
        $admin_capability = awpcp_admin_capability();

        $parent_page = $this->add_main_classifieds_admin_page(
            $parent_menu,
            'awpcp_main_classifieds_admin_page',
            $router
        );

        if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'awpcp-admin-upgrade' ) {
            $this->add_manual_upgrade_admin_page( $parent_page, __( 'Manual Upgrade', 'AWPCP' ), 'awpcp-admin-upgrade', $router );
        }

        $router->add_admin_subpage(
            $parent_page,
            __( 'Settings', 'AWPCP' ),
            awpcp_admin_page_title( __( 'Settings', 'AWPCP' ) ),
            'awpcp-admin-settings',
            'awpcp_settings_admin_page',
            $admin_capability,
            10
        );

        $router->add_admin_subpage(
            $parent_page,
            __( 'Listings', 'AWPCP' ),
            awpcp_admin_page_title( __( 'Manage Listings', 'AWPCP' ) ),
            'awpcp-admin-listings',
            'awpcp_manage_listings_admin_page',
            $admin_capability,
            20
        );

        $router->add_admin_subpage(
            $parent_page,
            __( 'Import Listings', 'AWPCP' ),
            awpcp_admin_page_title( __( 'Import Listings', 'AWPCP' ) ),
            'awpcp-import',
            'awpcp_import_listings_admin_page',
            $admin_capability,
            30
        );

        $router->add_admin_subpage(
            $parent_page,
            __( 'Categories', 'AWPCP' ),
            awpcp_admin_page_title( __( 'Manage Categories', 'AWPCP' ) ),
            'awpcp-admin-categories',
            'awpcp_categories_admin_page',
            $admin_capability,
            40
        );

        $router->add_admin_section(
            'awpcp.php::awpcp-admin-categories',
            'create-category',
            'awpcp-action',
            'create-category',
            'awpcp_create_category_admin_page'
        );

        $router->add_admin_section(
            'awpcp.php::awpcp-admin-categories',
            'update-category',
            'awpcp-action',
            'update-category',
            'awpcp_update_category_admin_page'
        );

        $router->add_admin_section(
            'awpcp.php::awpcp-admin-categories',
            'delete-category',
            'awpcp-action',
            'delete-category',
            'awpcp_delete_category_admin_page'
        );

        $router->add_admin_section(
            'awpcp.php::awpcp-admin-categories',
            'move-multiple-categories',
            'awpcp-move-multiple-categories',
            null,
            'awpcp_move_categories_admin_page'
        );

        $router->add_admin_section(
            'awpcp.php::awpcp-admin-categories',
            'delete-multiple-categories',
            'awpcp-delete-multiple-categories',
            null,
            'awpcp_delete_categories_admin_page'
        );

        $router->add_admin_subpage(
            $parent_page,
            __( 'Form Fields', 'AWPCP' ),
            awpcp_admin_page_title( __( 'Form Fields', 'AWPCP' ) ),
            'awpcp-form-fields',
            'awpcp_form_fields_admin_page',
            $admin_capability,
            50
        );

        $router->add_admin_subpage(
            $parent_page,
            __( 'Credit Plans', 'AWPCP' ),
            awpcp_admin_page_title( __( 'Manage Credit Plans', 'AWPCP' ) ),
            'awpcp-admin-credit-plans',
            'awpcp_credit_plans_admin_page',
            $admin_capability,
            60
        );

        $router->add_admin_custom_link(
            $parent_page,
            __( 'Manage Credit', 'AWPCP' ),
            'awpcp-manage-credits',
            $admin_capability,
            $this->get_manage_credits_section_url(),
            70
        );

        $router->add_admin_subpage(
            $parent_page,
            __( 'Fees', 'AWPCP' ),
            awpcp_admin_page_title( __( 'Manage Listings Fees', 'AWPCP' ) ),
            'awpcp-admin-fees',
            'awpcp_fees_admin_page',
            $admin_capability,
            80
        );

        $router->add_admin_subpage(
            $parent_page,
            __( 'Debug', 'AWPCP' ),
            awpcp_admin_page_title( __( 'Debug Information', 'AWPCP' ) ),
            'awpcp-debug',
            'awpcp_debug_admin_page',
            $admin_capability,
            9000
        );

        $router->add_admin_subpage(
            $parent_page,
            __( 'Uninstall', 'AWPCP' ),
            awpcp_admin_page_title( __( 'Uninstall', 'AWPCP' ) ),
            'awpcp-admin-uninstall',
            'awpcp_uninstall_admin_page',
            $admin_capability,
            9900
        );
	}

	public function notices() {
		if ( ! awpcp_current_user_is_admin() ) {
			return;
		}

		if ( awpcp_request_param( 'page', false ) == 'awpcp-admin-upgrade' ) {
			return;
		}

		if ( $this->upgrade_tasks->has_pending_tasks( 'plugin' ) ) {
			ob_start();
				include( AWPCP_DIR . '/admin/templates/admin-pending-manual-upgrade-notice.tpl.php' );
				$html = ob_get_contents();
			ob_end_clean();

			echo $html;

			return;
		}

		$show_quick_start_quide_notice = get_awpcp_option( 'show-quick-start-guide-notice' );
		$show_drip_autoresponder = get_awpcp_option( 'show-drip-autoresponder' );

		if ( $show_quick_start_quide_notice && is_awpcp_admin_page() && ! $show_drip_autoresponder ) {
			ob_start();
				include(AWPCP_DIR . '/admin/templates/admin-quick-start-guide-notice.tpl.php');
				$html = ob_get_contents();
			ob_end_clean();

			echo $html;
		}

		if (get_awpcp_option('show-widget-modification-notice')) {
			ob_start();
				include(AWPCP_DIR . '/admin/templates/admin-widget-modification-notice.tpl.php');
				$html = ob_get_contents();
			ob_end_clean();

			echo $html;
		}

		do_action( 'awpcp-admin-notices' );
	}

	/**
	 * Shows a notice if any of the AWPCP pages shares its name with the
	 * dynamic page View Categories.
	 *
	 * If a page share its name with the View Categories page, that page
	 * will become unreachable.
	 *
	 * @since 3.0.2
	 */
	public function check_duplicate_page_names() {
		global $wpdb;

		$view_categories_option = 'view-categories-page-name';
		$view_categories = sanitize_title( awpcp_get_page_name( $view_categories_option ) );
		$view_categories_url = awpcp_get_view_categories_url();

		$duplicates = array();
		$awpcp_pages = array();
		$wp_pages = array();

		$posts = get_posts( array( 'post_type' => 'page', 'name' => $view_categories ) );

		foreach ( $posts as $post ) {
			if ( $view_categories_url == get_permalink( $post->ID ) ) {
				$duplicates[] = $post;
			}
		}

		$pages = empty( $duplicates ) ? array() : awpcp_get_plugin_pages_refs();

		foreach ( $duplicates as $page ) {
			if ( isset( $pages[ $page->ID ] ) ) {
				$awpcp_pages[] = ucwords( awpcp()->settings->get_option_label( $pages[ $page->ID ]->page ) );
			} else {
				$wp_pages[] = $page->post_title;
			}
		}

		if ( !empty( $awpcp_pages ) || !empty( $wp_pages ) ) {
			$view_categories_label = awpcp()->settings->get_option_label( $view_categories_option );
			$view_categories_label = sprintf( '<strong>%s</strong>', ucwords( $view_categories_label ) );
		}

		if ( !empty( $awpcp_pages ) ) {
			$duplicated_pages = '<strong>' . join( '</strong>, <strong>', $awpcp_pages ) . '</strong>';

            $message = _n(
                '%1$s has the same name as the %2$s. That will cause %1$s to become unreachable. Please make sure you don\'t have duplicate page names.',
                '%1$s have the same name as the %2$s. That will cause %1$s to become unreachable. Please make sure you don\'t have duplicate page names.',
                count( $awpcp_pages),
                'another-wordpress-classifieds-plugin'
            );
			$message = sprintf( $message, $duplicated_pages, $view_categories_label );

			echo awpcp_print_error( $message );
		}

		if ( !empty( $wp_pages ) ) {
			$duplicated_pages = '<strong>' . join( '</strong>, <strong>', $wp_pages ) . '</strong>';

            $message = _n(
                'Page %1$s has the same name as the AWPCP %2$s. That will cause WordPress page %1$s to become unreachable. The %2$s is dynamic; you don\'t need to create a real WordPress page to show the list of cateogries, the plugin will generate it for you. If the WordPress page was created to show the default list of AWPCP categories, you can delete it and this error message will go away. Otherwise, please make sure you don\'t have duplicate page names.',
                'Pages %1$s have the same name as the AWPCP %2$s. That will cause WordPress pages %1$s to become unreachable. The %2$s is dynamic; you don\'t need to create a real WordPress page to show the list of cateogries, the plugin will generate it for you. If the WordPress pages were created to show the default list of AWPCP categories, you can delete them and this error message will go away. Otherwise, please make sure you don\'t have duplicate page names.',
                count( $wp_pages),
                'another-wordpress-classifieds-plugin'
            );
			$message = sprintf( $message, $duplicated_pages, $view_categories_label );

			echo awpcp_print_error( $message );
		}
	}


	public function init() {
		add_filter( 'parent_file', array( $this, 'parent_file' ) );
	}

	public function scripts() {
	}

	private function get_manage_credits_section_url() {
		$full_url = add_query_arg( 'action', 'awpcp-manage-credits', admin_url( 'users.php' ) );

		$domain = awpcp_request()->domain();

        if ( ! empty( $domain ) ) {
    		$domain_position = strpos( $full_url, $domain );
    		$url = substr( $full_url, $domain_position + strlen( $domain ) );
        } else {
            $url = $full_url;
        }

        return $url;
	}

	/**
	 * A hack to show the WP Users associated to a submenu under
	 * Classifieds menu.
	 *
	 * @since 3.0.2
	 */
	public function parent_file($parent_file) {
		global $current_screen, $submenu_file, $typenow;

		if ( $current_screen->base == 'users' && awpcp_request_param( 'action' ) == 'awpcp-manage-credits' ) {
			// make Classifieds menu the current menu
			$parent_file = 'awpcp.php';
			// highlight Manage Credits submenu in Classifieds menu
			$submenu_file = $this->get_manage_credits_section_url();
			// make $typenow non empty so Users menu is not highlighted
			// in _wp_menu_output, despite the fact we are showing the
			// All Users page.
			$typenow = 'hide-users-menu';
		}

		return $parent_file;
	}

	public function menu() {
		global $submenu;

		global $hasregionsmodule;
		global $hasextrafieldsmodule;

		$capability = awpcp_admin_capability();

		if ( $this->upgrade_tasks->has_pending_tasks() ) {
			// $parts = array($this->upgrade->title, $this->upgrade->menu, $this->upgrade->page);
			// $page = add_menu_page($parts[0], $parts[1], $capability, $parts[2], array($this->upgrade, 'dispatch'), MENUICO);

		} else {
			$parent = 'awpcp.php';

			// $parts = array( 'Classifieds', 'Classifieds', 'awpcp.php' );
			// $page = add_menu_page($parts[0], $parts[1], $capability, $parts[2], array($this, 'dispatch'), MENUICO);

			// // add hidden upgrade page, so the URL works even if there are no
			// // pending manual upgrades please note that this is a hack and
			// // it is important to use a subpage as parent page for it to work
			// $parts = array($this->title, $this->menu, $this->upgrade->page);
			// $page = add_submenu_page('awpcp-admin-uninstall', $parts[0], $parts[1], $capability, $parts[2], array($this->home, 'dispatch'), MENUICO);

			// $page = add_submenu_page(
			// 	$parent,
			// 	awpcp_admin_page_title( __( 'Settings', 'AWPCP' ) ),
			// 	__( 'Settings', 'AWPCP' ),
			// 	$capability,
			// 	'awpcp-admin-settings',
			// 	array( $this->settings, 'dispatch' )
			// );
			// add_action('admin_print_styles-' . $page, array($this->settings, 'scripts'));

			// if ( current_user_can( $capability ) ) {
			// 	$url = $this->get_manage_credits_section_url();
			// 	$submenu['awpcp.php'][] = array( __( 'Manage Credit', 'AWPCP' ), $capability, $url );
			// }

			// $parts = array($this->fees->title, $this->fees->menu, $this->fees->page);
			// $page = add_submenu_page($parent, $parts[0], $parts[1], $capability, $parts[2], array($this->fees, 'dispatch'));
			// add_action('admin_print_styles-' . $page, array($this->fees, 'scripts'));

			// add_submenu_page(
			// 	$parent,
			// 	awpcp_admin_page_title( __( 'Manage Categories', 'AWPCP' ) ),
			// 	__( 'Categories', 'AWPCP' ),
			// 	$capability,
			// 	'awpcp-admin-categories',
			// 	'awpcp_opsconfig_categories'
			// );

			// $page = add_submenu_page(
			// 	$parent,
			// 	$this->listings->title,
			// 	$this->listings->menu,
			// 	'manage_classifieds_listings',
			// 	'awpcp-listings',
			// 	array( $this->listings, 'dispatch' )
			// );
			// add_action('admin_print_styles-' . $page, array($this->listings, 'scripts'));

			// $this->form_fields = awpcp_form_fields_admin_page();
			// $parts = array( $this->form_fields->title, $this->form_fields->menu, $this->form_fields->page );
			// $page = add_submenu_page( $parent, $parts[0], $parts[1], $capability, 'awpcp-form-fields', array( $this->form_fields, 'dispatch' ) );
			// add_action( 'admin_print_styles-' . $page, array( $this->form_fields, 'scripts' ) );

			// // allow plugins to define additional sub menu entries
			// do_action('awpcp_admin_add_submenu_page', $parent, $capability);

			// if ($hasextrafieldsmodule) {
			// 	add_submenu_page($parent, __('Manage Extra Fields', 'another-wordpress-classifieds-plugin'), __('Extra Fields', 'another-wordpress-classifieds-plugin'), $capability, 'awpcp-admin-manual-upgrade', 'awpcp_add_new_field');
			// }

			// $hook = add_submenu_page($parent, __('Import Ad', 'AWPCP'), __('Import', 'AWPCP'), $capability, 'awpcp-import', array($this->importer, 'dispatch'));
			// add_action("load-{$hook}", array($this->importer, 'scripts'));

			// add_submenu_page($parent, 'Debug', 'Debug', $capability, 'awpcp-debug', array($this->debug, 'dispatch'));

			add_submenu_page($parent, __( 'Debug', 'another-wordpress-classifieds-plugin' ), __( 'Debug', 'another-wordpress-classifieds-plugin' ), $capability, 'awpcp-debug', array($this->debug, 'dispatch'));

			// // allow plugins to define additional menu entries
			// do_action('awpcp_add_menu_page');
		}
	}

    public function dispatch() {
    }

	public function upgrade() {
		global $plugin_page;

		if (!isset($this->upgrade) && isset($this->pages[$plugin_page]))
			$this->upgrade = new AWPCP_AdminUpgrade($plugin_page, $this->pages[$plugin_page]);
		return $this->upgrade->dispatch();
	}

    public function disable_quick_start_guide_notice() {
        global $awpcp;
        $awpcp->settings->update_option('show-quick-start-guide-notice', false);
        die('Success!');
    }

    public function disable_widget_modification_notice() {
        global $awpcp;
        $awpcp->settings->update_option('show-widget-modification-notice', false);
        die('Success!');
    }
}


// // if there's a page name collision remove AWPCP menus so that nothing can be accessed
// add_action('init', 'awpcp_pagename_warning_check', -1);
// function awpcp_pagename_warning_check() { 
// 	if (!get_option('awpcp_pagename_warning', false)) {
// 		return;
// 	}
//     remove_action('admin_menu', 'awpcp_launch');
// }


// // display a warning if necessary
// add_action('admin_notices', 'awpcp_pagename_warning', 10);
// function awpcp_pagename_warning() { 
// 	if (!get_option('awpcp_pagename_warning', false)) {
// 		return;
// 	}
// 	echo '<div id="message" class="error"><p><strong>';	
// 	echo 'WARNING: </strong>A page named AWPCP already exists. You must either delete that page and its subpages, or rename them before continuing with the plugin configuration.';
// 	echo '</p></div>';
// }




// START FUNCTION: Check if the user side classified page exists


function checkifclassifiedpage($pagename) {
	global $wpdb;

	$id = awpcp_get_page_id_by_ref( 'main-page-name' );
	$query = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE ID = %d';
	$page_id = intval( $wpdb->get_var( $wpdb->prepare( $query, $id ) ) );

	return $page_id === $id;
}

function awpcp_admin_categories_render_category_items($categories, &$children, $start=0, $per_page=10, &$count, $parent=0, $level=0) {
    $categories_collection = awpcp_categories_collection();

	$end = $start + $per_page;
	$items = array();

	foreach ($categories as $key => $category) {
		if ( $count >= $end ) break;

		if ( $category->parent != $parent ) continue;

		if ( $count == $start && $category->parent > 0 ) {
            try {
                $category_parent = $categories_collection->get( $category->parent );
                $items[] = awpcp_admin_categories_render_category_item( $category_parent, $level - 1, $start, $per_page );
            } catch ( AWPCP_Exception $e ) {
                // pass
            }
		}

		if ( $count >= $start ) {
			$items[] = awpcp_admin_categories_render_category_item( $category, $level, $start, $per_page  );
		}

		$count++;

		if ( isset( $children[ $category->term_id ] ) ) {
			$_children = awpcp_admin_categories_render_category_items( $categories, $children, $start, $per_page, $count, $category->term_id, $level + 1 );
			$items = array_merge( $items, $_children );
		}
	}

	return $items;
}

function awpcp_admin_categories_render_category_item($category, $level, $start, $per_page) {
	global $hascaticonsmodule, $awpcp_imagesurl;

	if ( function_exists( 'awpcp_get_category_icon' ) ) {
		$category_icon = awpcp_get_category_icon( $category );
	}

	if ( isset( $category_icon ) && !empty( $category_icon ) && function_exists( 'awpcp_category_icon_url' )  ) {
		$caticonsurl = awpcp_category_icon_url( $category_icon );
		$thecategoryicon = '<img style="vertical-align:middle;margin-right:5px;max-height:16px" src="%s" alt="%s" border="0" />';
		$thecategoryicon = sprintf( $thecategoryicon, esc_url( $caticonsurl ), esc_attr( $category->name ) );
	} else {
		$thecategoryicon = '';
	}

	$params = array( 'page' => 'awpcp-admin-categories', 'cat_ID' => $category->term_id );
	$admin_listings_url = add_query_arg( urlencode_deep( $params ), admin_url( 'admin.php' ) );

	$thecategory_parent_id = $category->parent;
	$thecategory_parent_name = stripslashes(get_adparentcatname($thecategory_parent_id));
	$thecategory_order = $category->order ? $category->order : 0;
	$thecategory_name = sprintf( '%s%s<a href="%s">%s</a>', str_repeat( '&mdash;&nbsp;', $level ),
															$thecategoryicon,
															esc_url( $admin_listings_url ),
															esc_attr( stripslashes( $category->name ) ) );

	$totaladsincat = total_ads_in_cat( $category->term_id );

	$params = array( 'cat_ID' => $category->term_id, 'offset' => $start, 'results' => $per_page );
	$admin_categories_url = add_query_arg( urlencode_deep( $params ), awpcp_get_admin_categories_url() );

	if ($hascaticonsmodule == 1 ) {
		$url = esc_url( add_query_arg( 'action', 'managecaticon', $admin_categories_url ) );
		$managecaticon = "<a href=\"$url\"><img src=\"$awpcp_imagesurl/icon_manage_ico.png\" alt=\"";
		$managecaticon.= __("Manage Category Icon", 'another-wordpress-classifieds-plugin');
		$managecaticon.= "\" title=\"" . __("Manage Category Icon", 'another-wordpress-classifieds-plugin') . "\" border=\"0\"/></a>";
	} else {
		$managecaticon = '';
	}

	$awpcpeditcategoryword = __("Edit Category",'another-wordpress-classifieds-plugin');
	$awpcpdeletecategoryword = __("Delete Category",'another-wordpress-classifieds-plugin');


	$row = '<tr>';
	$row.= '<td style="font-weight:normal; text-align: center;">' . $category->term_id . '</td>';
	$row.= "<td style=\"border-bottom:1px dotted #dddddd;font-weight:normal;\"><label><input type=\"checkbox\" name=\"category_to_delete_or_move[]\" value=\"{$category->term_id}\" /> $thecategory_name ($totaladsincat)</label></td>";
	$row.= "<td style=\"border-bottom:1px dotted #dddddd;font-weight:normal;\">$thecategory_parent_name</td>";
	$row.= "<td style=\"border-bottom:1px dotted #dddddd;font-weight:normal;\">$thecategory_order</td>";
	$row.= "<td style=\"border-bottom:1px dotted #dddddd;font-size:smaller;font-weight:normal;\">";
	$url = esc_url( add_query_arg( 'awpcp-action', 'edit-category', $admin_categories_url ) );
	$row.= "<a href=\"$url\"><img src=\"$awpcp_imagesurl/edit_ico.png\" alt=\"$awpcpeditcategoryword\" title=\"$awpcpeditcategoryword\" border=\"0\"/></a>";
	$url = esc_url( add_query_arg( 'awpcp-action', 'delete-category', $admin_categories_url ) );
	$row.= "<a href=\"$url\"><img src=\"$awpcp_imagesurl/delete_ico.png\" alt=\"$awpcpdeletecategoryword\" title=\"$awpcpdeletecategoryword\" border=\"0\"/></a>";
	$row.= $managecaticon;
	$row.= "</td>";
	$row.= "</tr>";

	return $row;
}

function awpcp_pages() {
	$pages = array('main-page-name' => array(get_awpcp_option('main-page-name'), '[AWPCP]'));
	return $pages + awpcp_subpages();
}

function awpcp_subpages() {
	$pages = array(
		'show-ads-page-name' => array(get_awpcp_option('show-ads-page-name'), '[AWPCPSHOWAD]'),
		'reply-to-ad-page-name' => array(get_awpcp_option('reply-to-ad-page-name'), '[AWPCPREPLYTOAD]'),
		'edit-ad-page-name' => array(get_awpcp_option('edit-ad-page-name'), '[AWPCPEDITAD]'),
		'place-ad-page-name' => array(get_awpcp_option('place-ad-page-name'), '[AWPCPPLACEAD]'),
		'renew-ad-page-name' => array(get_awpcp_option('renew-ad-page-name'), '[AWPCP-RENEW-AD]'),
		'browse-ads-page-name' => array(get_awpcp_option('browse-ads-page-name'), '[AWPCPBROWSEADS]'),
		'browse-categories-page-name' => array(get_awpcp_option('browse-categories-page-name'), '[AWPCPBROWSECATS]'),
		'search-ads-page-name' => array(get_awpcp_option('search-ads-page-name'), '[AWPCPSEARCHADS]'),
		'payment-thankyou-page-name' => array(get_awpcp_option('payment-thankyou-page-name'), '[AWPCPPAYMENTTHANKYOU]'),
		'payment-cancel-page-name' => array(get_awpcp_option('payment-cancel-page-name'), '[AWPCPCANCELPAYMENT]')
	);

	$pages = apply_filters('awpcp_subpages', $pages);

	return $pages;
}

function awpcp_create_pages($awpcp_page_name, $subpages=true) {
	global $wpdb;

	$refname = 'main-page-name';
	$date = date("Y-m-d");

	// create AWPCP main page if it does not exist
	if (!awpcp_find_page($refname)) {
		$awpcp_page = array(
			'post_author' => 1,
			'post_date' => $date,
			'post_date_gmt' => $date,
			'post_content' => '[AWPCPCLASSIFIEDSUI]',
			'post_title' => add_slashes_recursive($awpcp_page_name),
			'post_status' => 'publish',
			'post_name' => sanitize_title($awpcp_page_name),
			'post_modified' => $date,
			'comments_status' => 'closed',
			'post_content_filtered' => '[AWPCPCLASSIFIEDSUI]',
			'post_parent' => 0,
			'post_type' => 'page',
			'menu_order' => 0
		);
		$id = wp_insert_post($awpcp_page);

		awpcp_update_plugin_page_id( $refname, $id );
	} else {
		$id = awpcp_get_page_id_by_ref($refname);
	}

	// create subpages
	if ($subpages) {
		awpcp_create_subpages($id);
	}
}

function awpcp_create_subpages($awpcp_page_id) {
	$pages = awpcp_subpages();

	foreach ($pages as $key => $page) {
		awpcp_create_subpage($key, $page[0], $page[1], $awpcp_page_id);
	}
	
	do_action('awpcp_create_subpage');
}

/**
 * Creates a subpage of the main AWPCP page.
 * 
 * This functions takes care of checking if the main AWPCP
 * page exists, finding its id and verifying that the new
 * page doesn't exist already. Useful for module plugins.
 */
function awpcp_create_subpage($refname, $name, $shortcode, $awpcp_page_id=null) {
	global $wpdb;

	$id = 0;
	if (!empty($name)) {
		// it is possible that the main AWPCP page does not exist, in that case
		// we should create Subpages without a parent.
		if (is_null($awpcp_page_id) && awpcp_find_page('main-page-name')) {
			$awpcp_page_id = awpcp_get_page_id_by_ref('main-page-name');
		} else if (is_null(($awpcp_page_id))) {
			$awpcp_page_id = '';
		}

		if (!awpcp_find_page($refname)) {
			$id = maketheclassifiedsubpage($name, $awpcp_page_id, $shortcode);
		}
	}

	if ($id > 0) {
		awpcp_update_plugin_page_id( $refname, $id );
	}

	return $id;
}


function maketheclassifiedsubpage( $page_name, $parent_page_id, $short_code ) {
	$post_date = date("Y-m-d");
	$parent_page_id = intval( $parent_page_id );
	$post_name = sanitize_title( $page_name );
	$page_name = add_slashes_recursive( $page_name );

	$page_id = wp_insert_post( array(
		'post_date' => $post_date,
		'post_date_gmt' => $post_date,
		'post_title' => $page_name,
		'post_content' => $short_code,
		'post_status' => 'publish',
		'comment_status' => 'closed',
		'post_name' => $post_name,
		'post_modified' => $post_date,
		'post_modified_gmt' => $post_date,
		'post_content_filtered' => $short_code,
		'post_parent' => $parent_page_id,
		'post_type' => 'page',
	) );

	return $page_id;
}

/**
 * Calls awpcp-admin-sidebar filter to output Admin panel sidebar.
 *
 * To remove Admin panel sidebar remove the mentioned filter on init.
 *
 * XXX: this may belong to AdminPage class
 */
function awpcp_admin_sidebar($float='') {
	$html = apply_filters('awpcp-admin-sidebar', '', $float);
	return $html;
}

/**
 * XXX: this may belong to AdminPage class
 */
function awpcp_admin_sidebar_output($html, $float) {
	global $awpcp;

	$modules = array(
		'premium' => array(
			'installed' => array(),
			'not-installed' => array(),
		),
		'other' => array(
			'installed' => array(),
			'not-installed' => array(),
		),
	);

	$premium_modules = $awpcp->get_premium_modules_information();
	foreach ($premium_modules as $module) {
		if ( isset( $module['private'] ) && $module['private'] ) {
			continue;
		}

		if ( $module['installed'] ) {
			$modules['premium']['installed'][] = $module;
		} else {
			$modules['premium']['not-installed'][] = $module;
		}
	}

	$apath = get_option('siteurl') . '/wp-admin/images';
	$float = '' == $float ? 'float:right !important' : $float;
	$url = AWPCP_URL;

	ob_start();
		include(AWPCP_DIR . '/admin/templates/admin-sidebar.tpl.php');
		$content = ob_get_contents();
	ob_end_clean();

	return $content;
}
