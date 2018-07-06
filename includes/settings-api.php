<?php
/**
 * @package AWPCP\Settings
 */

/**
 * @since 3.6.1
 */
function awpcp_settings_api() {
    return awpcp()->container['Settings'];
}

/**
 * Allows access to stored values of the plugin's settings.
 */
class AWPCP_Settings_API {

    // phpcs:disable

	private $runtime_settings = array();

	public $setting_name = 'awpcp-options';
	public $options = array();

    /**
     * @var Settings
     */
    private $settings_manager;

	public function __construct( $settings_manager ) {
        $this->settings_manager = $settings_manager;

		$this->load();
	}

	public function load() {
		$options = get_option( $this->setting_name );

        if ( ! is_array( $options ) ) {
            $options = [];
        }

        $this->options = $options;
	}

	private function save_settings() {
		return update_option( $this->setting_name, $this->options );
	}

	/**
	 * Hook actions and filters required by AWPCP Settings
	 * to work.
	 */
	public function setup() {
		// setup validate functions
		add_filter('awpcp_validate_settings_general-settings',
				   array($this, 'validate_general_settings'), 10, 2);
		add_filter('awpcp_validate_settings_payment-settings',
				   array($this, 'validate_payment_settings'), 10, 2);
		add_filter('awpcp_validate_settings_registration-settings',
				   array($this, 'validate_registration_settings'), 10, 2);
		add_filter('awpcp_validate_settings_smtp-settings',
				   array($this, 'validate_smtp_settings'), 10, 2);

        add_filter(
            'awpcp_validate_settings_email-settings',
            array( $this, 'validate_email_settings' ),
            10,
            2
        );

        add_action( 'awpcp_settings_validated_pages-settings', array( $this, 'page_settings_validated' ), 10, 2 );
	}

    /**
     * @since 4.0.0     Updated to use Settings Manager.
     */
	public function get_option( $name, $default = '', $reload = false ) {
        if ( $reload ) {
            $this->load();
        }

        if ( isset( $this->options[ $name ] ) ) {
            return $this->prepare_option_value( $name, $this->options[ $name ] );
        }

        $default_value = $this->get_option_default_value( $name );

        if ( ! is_null( $default_value ) ) {
            return $this->prepare_option_value( $name, $default_value );
        }

        return $this->prepare_option_value( $name, $default );
	}

    /**
     * @since 4.0.0
     */
    private function prepare_option_value( $name, $value ) {
        // TODO: Provide a method for filtering options and move there the code below.
        $strip_slashes_from = [
            'awpcpshowtheadlayout',
            'sidebarwidgetaftertitle',
            'sidebarwidgetbeforetitle',
            'sidebarwidgetaftercontent',
            'sidebarwidgetbeforecontent',
            'adsense',
            'displayadlayoutcode',
        ];

        if ( in_array( $name, $strip_slashes_from, true ) ) {
            $value = stripslashes_deep( $value );
        }

        if ( ! is_array( $value ) ) {
            $value = trim( $value );
        }

        return $value;
    }

    /**
     * @since 4.0.0     Updated to use Settings Manager.
     */
	public function get_option_default_value( $name ) {
        $setting = $this->settings_manager->get_setting( $name );

        if ( isset( $setting['default'] ) ) {
            return $setting['default'];
		}

		return null;
	}

	/**
	 * @since 3.0.1
	 */
	public function get_option_label( $name ) {
        $setting = $this->settings_manager->get_setting( $name );

        if ( isset( $setting['name'] ) ) {
            return $setting['name'];
        }

        return null;
    }

	/**
	 * @param $force boolean - true to update unregistered options
	 */
	public function update_option( $name, $value, $force = false ) {
		if ( $force || array_key_exists( $name, $this->options, true ) ) {
			$this->options[ $name ] = $value;
			$this->save_settings();
			return true;
		}

		return false;
	}

	/**
	 * @since 3.2.2
	 */
	public function set_or_update_option( $name, $value ) {
		$this->options[ $name ] = $value;
		return $this->save_settings();
	}

	/**
	 * @since 3.3
	 */
	public function option_exists( $name ) {
		return isset( $this->options[ $name ] );
	}

	public function set_runtime_option( $name, $value ) {
		$this->runtime_settings[ $name ] = $value;
	}

	public function get_runtime_option( $name, $default = '' ) {
		if ( isset( $this->runtime_settings[ $name ] ) ) {
			return $this->runtime_settings[ $name ];
		} else {
			return $default;
		}
	}

    /**
     * TODO: Register section description renderer.
     */
	public function section_date_time_format($args) {
		$link = '<a href="http://codex.wordpress.org/Formatting_Date_and_Time">%s</a>.';
		echo sprintf( $link, __( 'Documentation on date and time formatting', 'another-wordpress-classifieds-plugin' ) );
	}
}
