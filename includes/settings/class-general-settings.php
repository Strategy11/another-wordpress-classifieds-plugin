<?php
/**
 * @package AWPCP\Settings
 */

function awpcp_general_settings() {
    return new AWPCP_GeneralSettings(
        awpcp_roles_and_capabilities(),
        awpcp()->container['Settings']
    );
}

/**
 */
class AWPCP_GeneralSettings {

    /**
     * @var RolesAndCapabilities
     */
    private $roles;

    /**
     * Constructor.
     */
    public function __construct( $roles, $settings ) {
        $this->roles    = $roles;
        $this->settings = $settings;
    }

    /**
     * @since 4.0.0     Updated to use Settings Manager.
     */
    public function register_settings( $settings_manager ) {
        $settings_manager->add_settings_group( [
            'id'       => 'general-settings',
            'name'     => __( 'General', 'another-wordpress-classifieds-plugin' ),
            'priority' => 5,
        ] );

        $this->register_general_settings( $settings_manager );
        $this->register_anti_spam_settings( $settings_manager );
        $this->register_adsense_settings( $settings_manager );
        $this->register_registration_settings( $settings_manager );

        $this->register_legacy_settings( $settings_manager );
    }

    /**
     * @since 4.0.0
     */
    private function register_general_settings( $settings_manager ) {
        $settings_manager->add_settings_subgroup( [
            'id' => 'general-settings',
            'name' => __( 'General Settings', 'another-wordpress-classifieds-plugin' ),
            'priority' => 5,
            'parent' => 'general-settings',
        ] );

        $settings_manager->add_settings_section( [
            'name'     => __( 'General Settings', 'another-wordpress-classifieds-plugin' ),
            'id'       => 'general-settings',
            'priority' => 5,
            'subgroup' => 'general-settings',
        ] );

        $settings_manager->add_setting( [
            'id'          => 'main_page_display',
            'name'        => __( 'Show Ad listings on main page', 'another-wordpress-classifieds-plugin' ),
            'type'        => 'checkbox',
            'default'     => 0,
            'description' => __( 'If unchecked only categories will be displayed', 'another-wordpress-classifieds-plugin' ),
            'section'     => 'general-settings',
        ] );

        $settings_manager->add_setting( [
            'id'      => 'view-categories-columns',
            'name'    => __( 'Category columns in View Categories page', 'another-wordpress-classifieds-plugin' ),
            'type'    => 'select',
            'default' => 2,
            'options' => [
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
            ],
            'section' => 'general-settings',
        ] );

        $settings_manager->add_setting( [
            'id'          => 'collapse-categories-columns',
            'name'        => __( 'Collapse Categories', 'another-wordpress-classifieds-plugin' ),
            'type'        =>'checkbox',
            'default'     => 0,
            'description' => __( 'If checked the list of sub-categories will be collapsed by default. Users would have to click the down arrow icon to expand the list and see the sub-categories.', 'another-wordpress-classifieds-plugin' ),
            'section'     => 'general-settings',
        ] );

        $settings_manager->add_setting( [
            'id'      => 'noadsinparentcat',
            'name'    => __( 'Prevent ads from being posted to top level categories?', 'another-wordpress-classifieds-plugin' ),
            'type'    => 'checkbox',
            'default' => 0,
            'section' => 'general-settings',
        ] );

        $settings_manager->add_setting( [
            'id'      => 'hide-empty-categories-dropdown',
            'name'    => __( 'Hide empty categories from dropdowns', 'another-wordpress-classifieds-plugin' ),
            'type'    => 'checkbox',
            'default' => 0,
            'section' => 'general-settings',
        ] );

        $settings_manager->add_setting( [
            'id'          => 'uiwelcome',
            'name'        => __( 'Welcome message in Classified page', 'another-wordpress-classifieds-plugin' ),
            'type'        => 'textarea',
            'default'     => __( 'Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a Classified Ad.', 'another-wordpress-classifieds-plugin' ),
            'description' => __( 'The welcome text for your classified page on the user side', 'another-wordpress-classifieds-plugin' ),
            'section'     => 'general-settings',
        ] );

        $settings_manager->add_setting( [
            'id'          => 'awpcpadminaccesslevel',
            'name'        => __( 'Who can access AWPCP Admin Dashboard', 'another-wordpress-classifieds-plugin' ),
            'type'        => 'radio',
            'default'     => 'admin',
            'description' => __( 'Role of WordPress users who can have admin access to Classifieds.', 'another-wordpress-classifieds-plugin' ),
            'options'     => [
                'admin'        => __( 'Administrator', 'another-wordpress-classifieds-plugin' ),
                'admin,editor' => __( 'Administrator & Editor', 'another-wordpress-classifieds-plugin' ),
            ],
            'section'     => 'general-settings',
        ] );

        $settings_manager->add_setting( [
            'id'          => 'awpcppagefilterswitch',
            'name'        => __( 'Enable page filter', 'another-wordpress-classifieds-plugin' ),
            'type'        => 'checkbox',
            'default'     => 1,
            'description' => __( 'Uncheck this if you need to turn off the AWPCP page filter that prevents AWPCP classifieds children pages from showing up in your wp pages menu (You might need to do this if for example the AWPCP page filter is messing up your page menu. It means you will have to manually exclude the AWPCP children pages from showing in your page list. Some of the pages really should not be visible to your users by default).', 'another-wordpress-classifieds-plugin'),
            'section'     => 'general-settings',
        ] );

        $settings_manager->add_setting( [
            'id'      => 'show-mobile-menu-expanded',
            'name'    => __( 'Auto-expand the Classifieds Menu for mobile devices?', 'another-wordpress-classifieds-plugin' ),
            'type'    => 'checkbox',
            'default' => false,
            'section' => 'general-settings',
        ] );

        $this->register_classifieds_management_panel_settings( $settings_manager );
        $this->register_date_and_time_format_settings( $settings_manager );
        $this->register_currency_format_settings( $settings_manager );
        $this->register_terms_of_service_settings( $settings_manager );
    }

    /**
     * @since 4.0.0
     */
    private function register_classifieds_management_panel_settings( $settings_manager ) {
        $settings_manager->add_settings_section( [
            'id'       => 'classifieds-management-panel',
            'name'     => __( 'Classifieds Management Panel', 'another-wordpress-classifieds-plugin' ),
            'priority' => 10,
            'subgroup' => 'general-settings',
        ] );

        $help_text = __( 'You must have registered users to use this setting. Turning it on will automatically enable "Require Registration" for AWPCP. Make sure you site allows users to register under <wp-settings-link>Settings->General</a>.', 'another-wordpress-classifieds-plugin' );
        $help_text = str_replace( '<wp-settings-link>', sprintf( '<a href="%s">', admin_url( 'options-general.php' ) ), $help_text );

        $settings_manager->add_setting( [
            'id'          => 'enable-user-panel',
            'name'        => __( 'Enable User Ad Management Panel', 'another-wordpress-classifieds-plugin' ),
            'type'        => 'checkbox',
            'default'     => 0,
            'description' => $help_text,
            'section'     => 'classifieds-management-panel',
        ] );
    }

    private function register_date_and_time_format_settings( $settings_manager ) {
		$description = '<a href="http://codex.wordpress.org/Formatting_Date_and_Time">%s</a>.';
		$description = sprintf( $description, __( 'Documentation on date and time formatting', 'another-wordpress-classifieds-plugin' ) );

        $settings_manager->add_settings_section( [
            'name'        => _x( 'Date & Time Format', 'settings', 'another-wordpress-classifieds-plugin' ),
            'id'          => 'date-time-format',
            'description' => $description,
            'priority'    => 20,
            'subgroup'    => 'general-settings',
        ] );

        $current_time = current_time( 'timestamp' );

        $settings_manager->add_setting( [
            'id'      => 'x-date-time-format',
            'name'    => __( 'Date Time Format', 'another-wordpress-classifieds-plugin' ),
            'type'    => 'radio',
            'default' => 'american',
            'options' => [
                'american' => sprintf( '<strong>%s</strong>: %s', __( 'American', 'another-wordpress-classifieds-plugin' ), awpcp_datetime( 'm/d/Y h:i:s', $current_time ) ),
                'european' => sprintf( '<strong>%s</strong>: %s', __( 'European', 'another-wordpress-classifieds-plugin' ), awpcp_datetime( 'd/m/Y H:i:s', $current_time ) ),
                'custom'   => __( 'Your own.', 'another-wordpress-classifieds-plugin' ),
            ],
            'section' => 'date-time-format',
        ] );

        $settings_manager->add_setting( [
            'id'      => 'date-format',
            'name'    => _x( 'Date Format', 'settings', 'another-wordpress-classifieds-plugin' ),
            'type'    => 'textfield',
            'default' => 'm/d/Y',
            'section' => 'date-time-format',
        ] );

        $settings_manager->add_setting( [
            'id'      => 'time-format',
            'name'    => _x( 'Time Format', 'settings', 'another-wordpress-classifieds-plugin' ),
            'type'    => 'textfield',
            'default' => 'h:i:s',
            'section' => 'date-time-format',
        ] );

        $example     = sprintf( '<strong>%s</strong>: <span example>%s</span>', _x( 'Example output', 'settings', 'another-wordpress-classifieds-plugin' ), awpcp_datetime( 'awpcp' ) );

        $description = _x( 'Full date/time output with any strings you wish to add. <date> and <time> are placeholders for date and time strings using the formats specified in the Date Format and Time Format settings above.', 'settings', 'another-wordpress-classifieds-plugin' );
        $description = esc_html( $description ) . '<br/>' . $example;

        $settings_manager->add_setting( [
            'id'          => 'date-time-format',
            'name'        => _x( 'Full Display String', 'settings', 'another-wordpress-classifieds-plugin' ),
            'type'        => 'textfield',
            'default'     => '<date> at <time>',
            'description' => $description,
            'section'     => 'date-time-format',
        ] );
    }

    private function register_currency_format_settings( $settings_manager ) {
        $group = 'general-settings';
        $key   = 'currency-format';

        $settings_manager->add_section($group, __('Currency Format', 'another-wordpress-classifieds-plugin'), 'currency-format', 20, array( $settings_manager, 'section' ) );

        $settings_manager->add_setting(
            $key,
            'currency-code',
            __( 'Currency code', 'another-wordpress-classifieds-plugin' ),
            'textfield',
            $this->settings->get_option( 'displaycurrencycode', 'USD' ),
            __( "Prices in listings pages and payment pages will be displayed using this currency. The currency symbol will be generated based on this code, but if the plugin doesn't know the symbol for your currency, it will use an uppercase version of the code itself.", 'another-wordpress-classifieds-plugin' )
        );

        $settings_manager->add_setting(
            $key,
            'currency-symbol',
            __( 'Currency symbol', 'another-wordpress-classifieds-plugin' ),
            'textfield',
            '',
            __( "Use this setting to overwrite the currency symbol shown in listings pages. If empty, the plugin will attempt to show one of the standard symbols for the selected currency code, but if the plugin doesn't know the symbol for your currency, it will use an uppercase version of the currency code.", 'another-wordpress-classifieds-plugin' )
        );

        $settings_manager->add_setting($key, 'thousands-separator', __('Thousands separator', 'another-wordpress-classifieds-plugin'), 'textfield', _x(',', 'This translation is deprecated. Please go to the Settings section to change the thousands separator.', 'another-wordpress-classifieds-plugin'), '');
        $settings_manager->add_setting($key, 'decimal-separator', __('Separator for the decimal point', 'another-wordpress-classifieds-plugin'), 'textfield', _x('.', 'This translation is deprecated. Please go to the Settings section to change the decimal separator.', 'another-wordpress-classifieds-plugin'), '');
        $settings_manager->add_setting($key, 'show-decimals', __('Show decimals in price', 'another-wordpress-classifieds-plugin'), 'checkbox', 1, _x('Uncheck to show prices without decimals. The value will be rounded.', 'settings', 'another-wordpress-classifieds-plugin'));

        $settings_manager->add_setting(
            $key,
            'show-currency-symbol',
            __( 'Show currency symbol', 'another-wordpress-classifieds-plugin' ),
            'radio',
            'show-currency-symbol-on-left',
            __( 'The currency symbol can be configured by changing the currency code in the settings above.', 'another-wordpress-classifieds-plugin' ),
            array(
                'options' => array(
                    'show-currency-symbol-on-left' => __( 'Show currency symbol on left', 'another-wordpress-classifieds-plugin' ),
                    'show-currency-symbol-on-right' => __( 'Show currency symbol on right', 'another-wordpress-classifieds-plugin' ),
                    'do-not-show-currency-symbol' => __( "Don't show currency symbol", 'another-wordpress-classifieds-plugin' ),
                ),
            )
        );

        $settings_manager->add_setting(
            $key,
            'include-space-between-currency-symbol-and-amount',
            __( 'Include a space between the currency symbol and the amount', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            1,
            ''
        );
    }

    /**
     * @since 4.0.0
     */
    private function register_terms_of_service_settings( $settings_manager ) {
        $group = 'general-settings';
        $key   = 'terms-of-service';

        $settings_manager->add_section( $group, __( 'Terms of Service', 'another-wordpress-classifieds-plugin' ), 'terms-of-service', 40, array( $settings_manager, 'section' ) );

        $settings_manager->add_setting( $key, 'requiredtos', __( 'Display and require Terms of Service', 'another-wordpress-classifieds-plugin' ), 'checkbox', 1, __( 'Display and require Terms of Service', 'another-wordpress-classifieds-plugin' ) );
        $settings_manager->add_setting( $key, 'tos', __( 'Terms of Service', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'Terms of service go here...', 'another-wordpress-classifieds-plugin' ), __( 'Terms of Service for posting Ads. Put in text or an URL starting with http. If you use an URL, the text box will be replaced by a link to the appropriate Terms of Service page', 'another-wordpress-classifieds-plugin' ) );
    }

    /**
     * @since 4.0.0
     */
    private function register_anti_spam_settings( $settings_manager ) {
        $group = 'anit-spam';
        $key   = 'anti-spam';

        $settings_manager->add_settings_subgroup( [
            'id'       => 'anit-spam',
            'name'     =>  __( 'Anti-SPAM', 'another-wordpress-classifieds-plugin' ),
            'priority' => 30,
            'parent'   => 'general-settings',
        ] );

        $settings_manager->add_section($group, __( 'Anti-SPAM', 'another-wordpress-classifieds-plugin' ), 'anti-spam', 10, array( $settings_manager, 'section' ) );

        if ( ! $this->settings->option_exists( 'useakismet' ) ) {
            $is_akismet_installed = function_exists( 'akismet_init' );
            $is_akismet_key_set = strlen( get_option( 'wordpress_api_key' ) ) > 0;
            $use_akismet_default_value = $is_akismet_installed && $is_akismet_key_set;
        } else {
            $use_akismet_default_value = $settings_manager->get_option( 'useakismet' );
        }

        $settings_manager->add_setting(
            $key,
            'use-akismet-in-place-listing-form',
            __( 'Use Akismet in Place Ad form', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $use_akismet_default_value,
            __( 'Use Akismet for Posting Ads (strong anti-spam).', 'another-wordpress-classifieds-plugin' )
        );

        $settings_manager->add_setting(
            $key,
            'use-akismet-in-reply-to-listing-form',
            __( 'Use Akismet in Reply to Ad form', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $use_akismet_default_value,
            __( 'Use Akismet for Contact Responses (strong anti-spam).', 'another-wordpress-classifieds-plugin' )
        );

        $is_captcha_enabled = $this->settings->get_option( 'captcha-enabled', $this->settings->get_option( 'contactformcheckhuman', 1 ) );

        $settings_manager->add_setting(
            $key,
            'captcha-enabled-in-place-listing-form',
            __( 'Enable CAPTCHA in Place Ad form', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $is_captcha_enabled,
            __( 'A CAPTCHA is a program to ensure only humans are posting Ads to your website. Using a CAPTCHA will reduce the SPAM and prevent bots from posting on your website. If checked, an additional form field will be added to the Place Ad form.', 'another-wordpress-classifieds-plugin' )
        );

        $settings_manager->add_setting(
            $key,
            'captcha-enabled-in-reply-to-listing-form',
            __( 'Enable CAPTCHA in Reply to Ad form', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $is_captcha_enabled,
            __( 'If checked, an additional form field will be added to the Reply to Ad form.', 'another-wordpress-classifieds-plugin' )
        );

        $options = array(
            'recaptcha' => __( 'reCAPTCHA (recommended)', 'another-wordpress-classifieds-plugin' ),
            'math' => __( 'Math', 'another-wordpress-classifieds-plugin' ),
        );

        $settings_manager->add_setting(
            $key,
            'captcha-provider',
            __( 'Type of CAPTCHA', 'another-wordpress-classifieds-plugin' ),
            'radio',
            'math',
            __( 'reCAPTCHA: Uses distorted images that only humans should be able to read (recommended).', 'another-wordpress-classifieds-plugin' ) . '<br/>' . __( 'Math: Asks user to solve a simple arithmetic operation.', 'another-wordpress-classifieds-plugin' ),
            array( 'options' => $options )
        );

        $settings_manager->add_setting( $key, 'math-captcha-max-number', __( 'Max number used in Math CAPTCHA', 'another-wordpress-classifieds-plugin' ), 'textfield', $this->settings->get_option( 'contactformcheckhumanhighnumval', 10 ), __( 'Highest number used in aithmetic operation.', 'another-wordpress-classifieds-plugin') );

        $link = sprintf( '<a href="%1$s">%1$s</a>', 'https://www.google.com/recaptcha/admin' );
        $help_text = sprintf( __( 'You can get an API key from %s.', 'another-wordpress-classifieds-plugin' ), $link );

        $settings_manager->add_setting( $key, 'recaptcha-public-key', __( 'reCAPTCHA Site Key', 'another-wordpress-classifieds-plugin' ), 'textfield', '', $help_text );
        $settings_manager->add_setting( $key, 'recaptcha-private-key', __( 'reCAPTCHA Secret Key', 'another-wordpress-classifieds-plugin' ), 'textfield', '',$help_text );
    }

    /**
     * @since 4.0.0
     */
    private function register_adsense_settings( $settings_manager ) {
        $settings_manager->add_settings_subgroup( [
            'name'     => __( 'AdSense', 'another-wordpress-classifieds-plugin' ),
            'id'       => 'adsense-settings',
            'priority' => 60,
            'parent'   => 'general-settings',
        ] );

        $group = 'adsense-settings';
        $key   = 'adsense-settings';

        $settings_manager->add_section( $group, __( 'AdSense Settings', 'another-wordpress-classifieds-plugin' ), 'adsense-settings', 10, array( $settings_manager, 'section' ) );

		$options = array(
			1 => __( 'Above Ad text.', 'another-wordpress-classifieds-plugin' ),
			2 => __( 'Under Ad text.', 'another-wordpress-classifieds-plugin' ),
			3 => __( 'Below Ad images.', 'another-wordpress-classifieds-plugin' ),
		);

		$settings_manager->add_setting( $key, 'useadsense', __( 'Activate AdSense', 'another-wordpress-classifieds-plugin'), 'checkbox', 1, '');
		$settings_manager->add_setting( $key, 'adsense', __( 'AdSense code', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'AdSense code', 'another-wordpress-classifieds-plugin' ), __( 'Your AdSense code (Best if 468x60 text or banner.)', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'adsenseposition', __( 'Show AdSense at position', 'another-wordpress-classifieds-plugin' ), 'radio', 2, '', array( 'options' => $options ) );
    }

    /**
     * @since 4.0.0
     */
    private function register_registration_settings( $settings_manager ) {
        $settings_manager->add_settings_subgroup( [
            'id'       => 'registration-settings',
            'name'     => __( 'Registration', 'another-wordpress-classifieds-plugin' ),
            'priority' => 20,
            'parent'   => 'general-settings',
        ] );

        $settings_manager->add_settings_section( [
            'id' => 'registration-settings',
            'name' => __( 'Registration Settings', 'another-wordpress-classifieds-plugin' ),
            'subgroup' => 'registration-settings',
        ] );

        $settings_manager->add_setting( [
            'id' => 'requireuserregistration',
            'name' => __( 'Place Ad requires user registration', 'another-wordpress-classifieds-plugin' ),
            'type' => 'checkbox',
            'description' =>  __( 'Only registered users will be allowed to post Ads.', 'another-wordpress-classifieds-plugin' ),
            'default' => 0,
            'section' => 'registration-settings',
        ] );

        $settings_manager->add_setting( [
            'id' => 'reply-to-ad-requires-registration',
            'name' => __( 'Reply to Ad requires user registration', 'another-wordpress-classifieds-plugin' ),
            'type' => 'checkbox',
            'default' => 0,
            'description' => __( 'Require user registration for replying to an Ad?', 'another-wordpress-classifieds-plugin' ),
            'section' => 'registration-settings',
        ] );

        $settings_manager->add_setting( [
            'id' => 'login-url',
            'name' => __( 'Login URL', 'another-wordpress-classifieds-plugin' ),
            'type' => 'textfield',
            'default' => '',
            'description' => __( 'Location of the login page. The value should be the full URL to the WordPress login page (e.g. http://www.awpcp.com/wp-login.php).', 'another-wordpress-classifieds-plugin' ) . '<br/><br/>' . __( 'IMPORTANT: Only change this setting when using a membership plugin with custom login pages or similar scenarios.', 'another-wordpress-classifieds-plugin' ),
            'section' => 'registration-settings',
        ] );

        $settings_manager->add_setting( [
            'id' => 'registrationurl',
            'name' => __( 'Custom Registration Page URL', 'another-wordpress-classifieds-plugin' ),
            'type' => 'textfield',
            'default' => '',
            'description' => __( 'Location of registration page. Value should be the full URL to the WordPress registration page (e.g. http://www.awpcp.com/wp-login.php?action=register).', 'another-wordpress-classifieds-plugin' ) . '<br/><br/>' . __( 'IMPORTANT: Only change this setting when using a membership plugin with custom login pages or similar scenarios.', 'another-wordpress-classifieds-plugin' ),
            'section' => 'registration-settings',
        ] );
    }

    public function validate_group_settings( $options, $group ) {
        $current_roles = $this->roles->get_administrator_roles_names();
        $selected_roles = $this->roles->get_administrator_roles_names_from_string( $options['awpcpadminaccesslevel'] );

        $removed_roles = array_diff( $current_roles, $selected_roles );
        $new_roles = array_diff( $selected_roles, $current_roles );

        if ( ! empty( $removed_roles ) ) {
            array_walk( $removed_roles, array( $this->roles, 'remove_administrator_capabilities_from_role' ) );
        }

        if ( ! empty( $new_roles ) ) {
            array_walk( $new_roles, array( $this->roles, 'add_administrator_capabilities_to_role' ) );
        }

        return $options;
    }

    /**
     * @since 4.0.0
     */
    private function register_legacy_settings( $settings_manager ) {
        $this->register_private_settings( $settings_manager );
        $this->register_modules_settings( $settings_manager );
        $this->register_appearance_settings( $settings_manager );
    }

    /**
     * @since 4.0.0
     */
    private function register_private_settings( $settings_manager ) {
        $settings_manager->add_settings_group( [
            'id'       => 'private-settings',
            'name'     => __( 'Private Settings', 'another-wordpress-classifieds-plugin' ),
            'priority' => 0,
        ] );

        $settings_manager->add_settings_subgroup( [
            'id'       => 'private-settings',
            'name'     => __( 'Private Settings', 'another-wordpress-classifieds-plugin' ),
            'priority' => 0,
            'parent'   => 'private-settings',
        ] );

        $settings_manager->add_settings_section( [
            'id'       => 'private-settings',
            'name'     => __( 'Private Settings', 'another-wordpress-classifieds-plugin' ),
            'subgroup' => 'private-settings',
        ] );
    }

    /**
     * @since 4.0.0
     */
    private function register_modules_settings( $settings_manager ) {
        $settings_manager->add_settings_group( [
            'id'       => 'modules-settings',
            'name'     => __( 'Modules', 'another-wordpress-classifieds-plugin' ),
            'priority' => 1000,
        ] );
    }

    /**
     * @since 4.0.0
     */
    private function register_appearance_settings( $settings_manager ) {
        $settings_manager->add_settings_group( [
            'name'     => __( 'Email', 'another-wordpress-classifieds-plugin' ),
            'id'       => 'email-settings',
            'priority' => 90,
        ] );

        $settings_manager->add_settings_subgroup( [
            'name'   => __( 'Email', 'another-wordpress-classifieds-plugin' ),
            'id'     => 'email-settings',
            'parent' => 'email-settings',
        ] );

		// Section: General Email Settings

        $group = 'email-settings';
        $key = 'default';

        $settings_manager->add_section($group, __('General Email Settings', 'another-wordpress-classifieds-plugin'), 'default', 20, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'admin-recipient-email', __( 'TO email address for outgoing emails', 'another-wordpress-classifieds-plugin' ), 'textfield', '', __( 'Emails are sent to your WordPress admin email. If you prefer to receive emails in a different address, please enter it here.', 'another-wordpress-classifieds-plugin' ) );

		$settings_manager->add_setting(
			$key,
			'awpcpadminemail',
			__( 'FROM email address for outgoing emails', 'another-wordpress-classifieds-plugin' ),
			'textfield',
			'',
			__( 'Emails go out using your WordPress admin email. If you prefer to use a different email enter it here. Some servers will not process outgoing emails that have an email address from gmail, yahoo, hotmail and other free email services in the FROM field. Some servers will also not process emails that have an email address that is different from the email address associated with your hosting account in the FROM field. If you are with such a webhost you need to make sure your WordPress admin email address is tied to your hosting account.', 'another-wordpress-classifieds-plugin' )
		);

		$setting_label = __( 'Use wordpress@<website-domain> as the FROM email address for outgoing emails.', 'another-wordpress-classifieds-plugin' );
		$setting_label = str_replace( '<website-domain>', awpcp_request()->domain( false ), $setting_label );

		$settings_manager->add_setting(
			$key,
			'sent-emails-using-wordpress-email-address',
			$setting_label,
			'checkbox',
			0,
			__( "That's the address WordPress uses to send its emails. If you are receiving the registration emails and other WordPress notifications succesfully, then you may want to enable this setting to use the same email address for all the outgoing messages. If enabled, the FROM email address for outgoing emails setting is ignored.", 'another-wordpress-classifieds-plugin' )
		);

		$settings_manager->add_setting( $key, 'usesenderemailinsteadofadmin', __( 'Use sender email for reply messages', 'another-wordpress-classifieds-plugin' ), 'checkbox', 0, __( 'Check this to use the name and email of the sender in the FROM field when someone replies to an ad. When unchecked the messages go out with the website name and WP admin email address in the from field. Some servers will not process outgoing emails that have an email address from gmail, yahoo, hotmail and other free email services in the FROM field. Some servers will also not process emails that have an email address that is different from the email address associated with your hosting account in the FROM field. If you are with such a webhost you need to leave this option unchecked and make sure your WordPress admin email address is tied to your hosting account.', 'another-wordpress-classifieds-plugin' ) );

        /* translators: full-email-address=John Doe <john.doe@example.com>, short-email-address=john.doe@example.com */
        $description = __( 'If checked, whenever the name of the recipient is available, emails will be sent to <full-email-address> instead of just <short-email-address>. Some email servers, however, have problems handling email address that include the name of the recipient. If emails sent by the plugin are not being delivered properly, try unchecking this settting.' );
        $description = str_replace( '<full-email-address>', '<strong>' . esc_html( 'John Doe <john.doe@example.com>' ) . '</strong>', $description );
        $description = str_replace( '<short-email-address>', '<strong>' . esc_html( 'john.doe@example.com' ) . '</strong>', $description );

        $settings_manager->add_setting(
            $key,
            'include-recipient-name-in-email-address',
            __( 'Include the name of the recipient in the email address', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            1,
            $description
        );

		$settings_manager->add_setting( $key, 'include-ad-access-key', __( 'Include Ad access key in email messages', 'another-wordpress-classifieds-plugin' ), 'checkbox', 1, __( "Include Ad access key in email notifications. You may want to uncheck this option if you are using the Ad Management panel, but is not necessary.", 'another-wordpress-classifieds-plugin' ) );

		// Section: Ad Posted Message

        $key = 'ad-posted-message';

        $settings_manager->add_section($group, __('Ad Posted Message', 'another-wordpress-classifieds-plugin'), 'ad-posted-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'listingaddedsubject', __( 'Subject for Ad posted notification email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'Your Classified Ad listing has been submitted', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when someone posts an Ad', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'listingaddedbody', __( 'Body for Ad posted notification email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'Thank you for submitting your Classified Ad. The details of your ad are shown below.', 'another-wordpress-classifieds-plugin' ), __( 'Message body text for email sent out when someone posts an Ad', 'another-wordpress-classifieds-plugin' ) );

		// Section: Reply to Ad Message

        $key = 'reply-to-ad-message';

        $settings_manager->add_section($group, __('Reply to Ad Message', 'another-wordpress-classifieds-plugin'), 'reply-to-ad-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'contactformsubjectline', __( 'Subject for Reply to Ad email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'Response to your AWPCP Demo Ad', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when someone replies to Ad', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'contactformbodymessage', __( 'Body for Reply to Ad email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'Someone has responded to your AWPCP Demo Ad', 'another-wordpress-classifieds-plugin' ), __( 'Message body text for email sent out when someone replies to Ad', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'notify-admin-about-contact-message', __( 'Notify admin about contact message', 'another-wordpress-classifieds-plugin' ), 'checkbox', 1, __( 'An email will be sent to the administrator every time a visitor sends a message to one of the Ad posters through the Reply to Ad page.', 'another-wordpress-classifieds-plugin' ) );

		// Section: Request Ad Message

        $key = 'request-ad-message';

        $settings_manager->add_section($group, __('Resend Access Key Message', 'another-wordpress-classifieds-plugin'), 'request-ad-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'resendakeyformsubjectline', __( 'Subject for Request Ad Access Key email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( "The Classified Ad's ad access key you requested", 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when someone requests their ad access key resent', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'resendakeyformbodymessage', __( 'Body for Request Ad Access Key email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( "You asked to have your Classified Ad's access key resent. Below are all the Ad access keys in the system that are tied to the email address you provided", 'another-wordpress-classifieds-plugin' ), __('Message body text for email sent out when someone requests their ad access key resent', 'another-wordpress-classifieds-plugin' ) );

		// Section: Verify Email Message

        $key = 'verify-email-message';

        $settings_manager->add_section($group, __('Verify Email Message', 'another-wordpress-classifieds-plugin'), 'verify-email-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'verifyemailsubjectline', __( 'Subject for Verification email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'Verify the email address used for Ad $title', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out to verify the email address.', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'verifyemailbodymessage', __( 'Body for Verification email', 'another-wordpress-classifieds-plugin' ), 'textarea', _x( "Hello \$author_name \n\nYou recently posted the Ad \$title to \$website_name. \n\nIn order to complete the posting process you have to verify your email address. Please click the link below to complete the verification process. You will be redirected to the website where you can see your Ad. \n\n\$verification_link \n\nAfter you verify your email address, the administrator will be notified about the new Ad. If moderation is enabled, your Ad will remain in a disabled status until the administrator approves it.\n\n\$website_name\n\n\$website_url", 'another-wordpress-classifieds-plugin' ), __('You can use the following placeholders to personalize the body of the email: $title, $author_name,$verification_email, $website_name, $website_url.', 'another-wordpress-classifieds-plugin' ) );

		// Section: Incomplete Payment Message

        $key = 'incomplete-payment-message';

        $settings_manager->add_section($group, __('Incomplete Payment Message', 'another-wordpress-classifieds-plugin'), 'incomplete-payment-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'paymentabortedsubjectline', __( 'Subject for Incomplete Payment email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'There was a problem processing your payment', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when the payment processing does not complete', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'paymentabortedbodymessage', __( 'Body for Incomplete Payment email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'There was a problem encountered during your attempt to submit payment. If funds were removed from the account you tried to use to make a payment please contact the website admin or the payment website customer service for assistance.', 'another-wordpress-classifieds-plugin' ), __( 'Message body text for email sent out when the payment processing does not complete', 'another-wordpress-classifieds-plugin' ) );

		// Section: Renew Ad Message

        $key = 'renew-ad-message';

        $settings_manager->add_section($group, __('Renew Ad Message', 'another-wordpress-classifieds-plugin'), 'renew-ad-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'renew-ad-email-subject', __( 'Subject for Renew Ad email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'Your classifieds listing Ad will expire in %d days.', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when an Ad is about to expire.', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'renew-ad-email-body', __( 'Body for Renew Ad email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'This is an automated notification that your Classified Ad will expire in %d days.', 'another-wordpress-classifieds-plugin' ), __( 'Message body text for email sent out when an Ad is about to expire. Use %d as placeholder for the number of days before the Ad expires.', 'another-wordpress-classifieds-plugin' ) );

		// Section: Ad Renewed Message

        $key = 'ad-renewed-message';

        $settings_manager->add_section($group, __('Ad Renewed Message', 'another-wordpress-classifieds-plugin'), 'ad-renewed-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'ad-renewed-email-subject', __( 'Subject for Ad Renewed email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'Your classifieds listing "%s" has been successfully renewed.', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when an Ad is successfully renewed.', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'ad-renewed-email-body', __( 'Body for Renew Ad email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'Your classifieds listing Ad has been successfully renewed. More information below:', 'another-wordpress-classifieds-plugin' ), __( 'Message body text for email sent out when an Ad is successfully renewed. ', 'another-wordpress-classifieds-plugin' ) );

		// Section: Ad Expired Message

        $key = 'ad-expired-message';

        $settings_manager->add_section($group, __('Ad Expired Message', 'another-wordpress-classifieds-plugin'), 'ad-expired-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'adexpiredsubjectline', __( 'Subject for Ad Expired email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'Your classifieds listing at %s has expired', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when an ad has auto-expired', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'adexpiredbodymessage', __( 'Body for Ad Expired email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'This is an automated notification that your Classified Ad has expired.', 'another-wordpress-classifieds-plugin' ), __( 'Message body text for email sent out when an ad has auto-expired', 'another-wordpress-classifieds-plugin' ) );

		// Section: Advanced Email Configuration

        $key = 'advanced';

        $settings_manager->add_section( $group, __( 'Advanced Email Configuration', 'another-wordpress-classifieds-plugin' ), 'advanced', 30, array( $settings_manager, 'section' ) );

		$settings_manager->add_setting( $key, 'usesmtp', __( 'Enable external SMTP server', 'another-wordpress-classifieds-plugin' ), 'checkbox', 0, __( 'Enabled external SMTP server (if emails not processing normally).', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'smtphost', __( 'SMTP host', 'another-wordpress-classifieds-plugin' ), 'textfield', 'mail.example.com', __( 'SMTP host (if emails not processing normally).', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'smtpport', __( 'SMTP port', 'another-wordpress-classifieds-plugin' ), 'textfield', '25', __( 'SMTP port (if emails not processing normally).', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'smtpusername', __( 'SMTP username', 'another-wordpress-classifieds-plugin' ), 'textfield', 'smtp_username', __( 'SMTP username (if emails not processing normally).', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'smtppassword', __( 'SMTP password', 'another-wordpress-classifieds-plugin' ), 'password', '', __( 'SMTP password (if emails not processing normally).', 'another-wordpress-classifieds-plugin' ) );

		// Group: Facebook

        $group = 'facebook-settings';

        $settings_manager->add_settings_group( [
            'name'     => __( 'Facebook', 'another-wordpress-classifieds-plugin' ),
            'id'       => 'facebook-settings',
            'priority' => 100,
        ] );

        $settings_manager->add_settings_subgroup( [
            'name'     => __( 'Facebook', 'another-wordpress-classifieds-plugin' ),
            'id'       => 'facebook-settings',
            'parent'   => 'facebook-settings',
        ] );

        $key = 'general';

        $settings_manager->add_section( $group, __( 'General Settings', 'another-wordpress-classifieds-plugin' ), 'general', 10, array( $settings_manager, 'section' ) );

		$settings_manager->add_setting( $key, 'sends-listings-to-facebook-automatically', __( 'Send Ads to Facebook Automatically', 'another-wordpress-classifieds-plugin' ), 'checkbox', 1, __( 'If checked, Ads will be posted to Facebook shortly after they are posted, enabled or edited, whichever occurs first. Ads will be posted only once. Please note that disabled Ads cannot be posted to Facebook.', 'another-wordpress-classifieds-plugin' ) );
    }
}
