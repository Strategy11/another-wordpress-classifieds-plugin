<?php
/**
 * @package AWPCP\Settings
 */

/**
 * Constructor function.
 */
function awpcp_general_settings() {
    return new AWPCP_GeneralSettings(
        awpcp_roles_and_capabilities(),
        awpcp()->container['Settings']
    );
}

/**
 * Register general plugin settings.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
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
        $this->register_date_and_time_format_settings( $settings_manager );
        $this->register_currency_format_settings( $settings_manager );
        $this->register_anti_spam_settings( $settings_manager );
        $this->register_adsense_settings( $settings_manager );
        $this->register_registration_settings( $settings_manager );

        $this->register_legacy_settings( $settings_manager );
    }

    // phpcs:disable

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

        $this->register_general_settings_section( $settings_manager );
        $this->register_classifieds_management_panel_settings( $settings_manager );
        $this->register_terms_of_service_settings( $settings_manager );
    }

    /**
     * @since 4.0.0
     */
    private function register_general_settings_section( $settings_manager ) {
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
            'name'        => __( 'Welcome message in Classifieds page', 'another-wordpress-classifieds-plugin' ),
            'type'        => 'textarea',
            'default'     => __( 'Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a Classified Ad.', 'another-wordpress-classifieds-plugin' ),
            'description' => __( 'The welcome text for your classifieds page on the user side', 'another-wordpress-classifieds-plugin' ),
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
    private function register_date_and_time_format_settings( $settings_manager ) {
        $settings_manager->add_settings_subgroup( [
            'id'       => 'date-time-format-settings',
            'name'     => _x( 'Date & Time Format', 'settings', 'another-wordpress-classifieds-plugin' ),
            'priority' => 20,
            'parent'   => 'general-settings',
        ] );

		$description = '<a href="http://codex.wordpress.org/Formatting_Date_and_Time">%s</a>.';
		$description = sprintf( $description, __( 'Documentation on date and time formatting', 'another-wordpress-classifieds-plugin' ) );

        $settings_manager->add_settings_section( [
            'name'        => _x( 'Date & Time Format', 'settings', 'another-wordpress-classifieds-plugin' ),
            'id'          => 'date-time-format',
            'description' => $description,
            'subgroup'    => 'date-time-format-settings',
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
            'id'       => 'date-format',
            'name'     => _x( 'Date Format', 'settings', 'another-wordpress-classifieds-plugin' ),
            'type'     => 'textfield',
            'default'  => 'm/d/Y',
            'behavior' => [
                'enabledIfMatches' => 'x-date-time-format=custom',
            ],
            'section'  => 'date-time-format',
        ] );

        $settings_manager->add_setting( [
            'id'       => 'time-format',
            'name'     => _x( 'Time Format', 'settings', 'another-wordpress-classifieds-plugin' ),
            'type'     => 'textfield',
            'default'  => 'h:i:s',
            'behavior' => [
                'enabledIfMatches' => 'x-date-time-format=custom',
            ],
            'section'  => 'date-time-format',
        ] );

        $example     = sprintf( '%s: <strong example>%s</strong>', _x( 'Example output', 'settings', 'another-wordpress-classifieds-plugin' ), awpcp_datetime( 'awpcp' ) );

        $description = _x( 'Full date/time output with any strings you wish to add. <date> and <time> are placeholders for date and time strings using the formats specified in the Date Format and Time Format settings above.', 'settings', 'another-wordpress-classifieds-plugin' );
        $description = esc_html( $description ) . '<br/><br/>' . $example;

        $settings_manager->add_setting( [
            'id'          => 'date-time-format',
            'name'        => _x( 'Full Display String', 'settings', 'another-wordpress-classifieds-plugin' ),
            'type'        => 'textfield',
            'default'     => '<date> at <time>',
            'description' => $description,
            'behavior'    => [
                'enabledIfMatches' => 'x-date-time-format=custom',
            ],
            'section'     => 'date-time-format',
        ] );
    }

    /**
     * @since 4.0.0
     */
    private function register_currency_format_settings( $settings_manager ) {
        $key   = 'currency-format';

        $settings_manager->add_settings_subgroup( [
            'id'      => 'currency-format-settings',
            'name'    => _x( 'Currency Format', 'settings', 'another-wordpress-classifieds-plugin' ),
            'priority' => 30,
            'parent'  => 'general-settings',
        ] );

        $settings_manager->add_settings_section( [
            'id'       => 'currency-format',
            'name'     => __('Currency Format', 'another-wordpress-classifieds-plugin'),
            'subgroup' => 'currency-format-settings',
        ] );

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
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function register_anti_spam_settings( $settings_manager ) {
        $group = 'anit-spam';
        $key   = 'anti-spam';

        $settings_manager->add_settings_subgroup( [
            'id'       => 'anit-spam',
            'name'     =>  __( 'Anti-SPAM', 'another-wordpress-classifieds-plugin' ),
            'priority' => 50,
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
            'priority' => 40,
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

    /**
     * @since 4.0.0
     */
    private function register_legacy_settings( $settings_manager ) {
        $this->register_private_settings( $settings_manager );
        $this->register_modules_settings( $settings_manager );
        $this->register_facebook_settings( $settings_manager );
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
    private function register_facebook_settings( $settings_manager ) {
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

        $settings_manager->add_settings_section(
            [
                'id'       => 'general',
                'name'     => __( 'General Settings', 'another-wordpress-classifieds-plugin' ),
                'priority' => 10,
                'subgroup' => 'facebook-settings',
            ]
        );

        $settings_manager->add_setting(
            [
                'id'          => 'facebook-integration-method',
                'name'        => __( 'Facebook Integration Method', 'another-wordpress-classifieds-plugin' ),
                'type'        => 'radio',
                'default'     => 'webhooks',
                'description' => __( 'Please note that sending ads to Facebook Groups is currently not available using Webhooks, after Facebook significantly reduced access to their APIs across all apps. You can read more about these changes here: <a href="https://developers.facebook.com/blog/post/2018/04/04/facebook-api-platform-product-changes/">https://developers.facebook.com/blog/post/2018/04/04/facebook-api-platform-product-changes/</a>', 'another-wordpress-classifieds-plugin' ),
                'options'     => [
                    'facebook-api' => __( 'Facebook API', 'another-wordpress-classifieds-plugin' ),
                    'webhooks'     => __( 'Zapier/IFTTT Webhooks', 'another-wordpress-classifieds-plugin' ),
                ],
                'section'     => 'general',
            ]
        );

        $settings_manager->add_setting(
            [
                'id'          => 'sends-listings-to-facebook-automatically',
                'name'        => __( 'Send Ads to Facebook Automatically', 'another-wordpress-classifieds-plugin' ),
                'type'        => 'checkbox',
                'default'     => 1,
                'description' => __( 'If checked, Ads will be sent to Facebook shortly after they are posted, enabled or edited, whichever occurs first. Please note that ads will be sent only once and disabled ads cannot be sent to Facebook.', 'another-wordpress-classifieds-plugin' ),
                'section'     => 'general',
            ]
        );

        $settings_manager->add_setting(
            [
                'id'          => 'clear-facebook-cache-for-ads-pages',
                'name'        => __( 'Ask Facebook to clear cache for ads pages', 'another-wordpress-classifieds-plugin' ),
                'type'        => 'checkbox',
                'default'     => false,
                'description' => __( 'Clearing Facebook cache is useful to ensure users always see the latest version when the ad is shared on Facebook Pages, Groups and user feeds.' ),
                'section'     => 'general',
            ]
        );

        $key = $settings_manager->add_settings_section(
            [
                'id'       => 'facebook-application',
                'name'     => __( 'Facebook Application', 'another-wordpress-classifieds-plugin' ),
                'priority' => 20,
                'callback' => array( $this, 'facebook_application_settings_section' ),
                'subgroup' => 'facebook-settings',
            ]
        );

        $settings_manager->add_setting(
            [
                'id'          => 'facebook-app-id',
                'name'        => __( 'App Id', 'another-wordpress-classifieds-plugin' ),
                'type'        => 'textfield',
                'default'     => '',
                'description' => __( 'An application identifier associates your site, its pages, and visitor actions with a registered Facebook application.', 'another-wordpress-classifieds-plugin' ),
                'section'     => 'facebook-application',
            ]
        );

        $settings_manager->add_setting(
            [
                'id'          => 'facebook-app-secret',
                'name'        => __( 'App Secret', 'another-wordpress-classifieds-plugin' ),
                'type'        => 'textfield',
                'default'     => '',
                'description' => __( 'An application secret is a secret shared between Facebook and your application, similar to a password.', 'another-wordpress-classifieds-plugin' ),
                'section'     => 'facebook-application',
            ]
        );

        $settings_manager->add_settings_section(
            [
                'id'       => 'facebook-user-authorization',
                'name'     => __( 'Facebook User Authorization', 'another-wordpress-classifieds-plugin' ),
                'priority' => 30,
                'callback' => array( $this, 'facebook_user_authorization_section' ),
                'subgroup' => 'facebook-settings',
            ]
        );

        $settings_manager->add_setting(
            [
                'id'          => 'facebook-user-access-token',
                'name'        => __( 'User Access Token', 'another-wordpress-classifieds-plugin' ),
                'type'        => 'textfield',
                'default'     => '',
                'description' => __( 'You can manually enter your user access token (if you know it) or log in to Facebook to get one using the link above.', 'another-wordpress-classifieds-plugin' ),
                'section'     => 'facebook-user-authorization',
            ]
        );

        $settings_manager->add_settings_section(
            [
                'id'       => 'facebook-page-and-group-selection',
                'name'     => __( 'Facebook Page and Group Selection', 'another-wordpress-classifieds-plugin' ),
                'priority' => 40,
                'callback' => array( $this, 'facebook_page_and_group_selection_section' ),
                'subgroup' => 'facebook-settings',
            ]
        );

        $settings_manager->add_setting(
            [
                'id'          => 'facebook-page',
                'name'        => __( 'Facebook Page', 'another-wordpress-classifieds-plugin' ),
                'type'        => 'radio',
                'default'     => '',
                'description' => '',
                'options'     => array( $this, 'facebook_page_options' ),
                'section'     => 'facebook-page-and-group-selection',
            ]
        );

        $settings_manager->add_setting(
            [
                'id'          => 'facebook-group',
                'name'        => __( 'Facebook Group', 'another-wordpress-classifieds-plugin' ),
                'type'        => 'radio',
                'default'     => '',
                'description' => '',
                'options'     => array( $this, 'facebook_group_options' ),
                'section'     => 'facebook-page-and-group-selection',
            ]
        );

        $settings_manager->add_setting(
            [
                'id'          => 'facebook-page-access-token',
                'name'        => __( 'Facebook Page Access Token', 'another-wordpress-classifieds-plugin' ),
                'type'        => 'textfield',
                'default'     => '',
                'description' => '',
                'readonly'    => true,
                'section'     => 'facebook-page-and-group-selection',
            ]
        );

        $settings_manager->add_settings_section(
            [
                'id'       => 'zapier',
                'name'     => __( 'Zapier Integration', 'another-wordpress-classifieds-plugin' ),
                'priority' => 50,
                'subgroup' => 'facebook-settings',
            ]
        );

        $settings_manager->add_setting(
            [
                'id'          => 'zapier-webhook-for-facebook-page-integration',
                'name'        => __( 'Zapier webhook used to send ads to a Facebook page', 'another-wordpress-classifieds-plugin' ),
                'type'        => 'textfield',
                'default'     => '',
                'description' => __( 'The plugin will post information to this URL the first time an ad becomes publicly available (after they are posted, enabled or edited) on the website or using the Send to Facebook Page action from the list of classified ads. Disabled ads are excluded.', 'another-wordpress-classifieds-plugin' ),
                'section'     => 'zapier',
            ]
        );

        $key = $settings_manager->add_settings_section(
            [
                'name'     => __( 'IFTTT Integration', 'another-wordpress-classifieds-plugin' ),
                'id'       => 'ifttt',
                'priority' => 60,
                'subgroup' => 'facebook-settings',
            ]
        );

        $settings_manager->add_setting(
            [
                'id'          => 'ifttt-webhook-base-url-for-facebook-page-integration',
                'name'        => __( 'URL used to send requests to IFTTT Webhooks service', 'another-wordpress-classifieds-plugin' ),
                'type'        => 'textfield',
                'default'     => '',
                'description' => __( 'The plugin will post information to the Webhooks service the first time an ad becomes publicly available (after they are posted, enabled or edited) on the website or when someone uses the Send to Facebook Page action from the list of classified ads. Disabled ads are excluded.', 'another-wordpress-classifieds-plugin' ),
                'section'     => 'ifttt',
            ]
        );

        $settings_manager->add_setting(
            [
                'id'          => 'ifttt-webhook-event-name-for-facebook-page-integration',
                'name'        => __( 'The name of the event that will be posted to the Webhooks service', 'another-wordpress-classifieds-plugin' ),
                'type'        => 'textfield',
                'default'     => '',
                'description' => __( 'The plugin will post information about new ads to a unique URL built using the Webhooks URL and Event Name you define.', 'another-wordpress-classifieds-plugin' ),
                'section'     => 'ifttt',
            ]
        );
    }

    /**
      * @since 3.8.6
      */
     public function facebook_application_settings_section( $args ) {
         $content = __( 'You can find your application information in the <a>Facebook Developer Apps</a> page.', 'another-wordpress-classifieds-plugin' );
         $content = str_replace( '<a>', '<a href="https://developers.facebook.com/apps/" target="_blank">', $content );

         echo $content; // XSS Ok.
     }

    /**
     * @since 3.8.6
     */
    public function facebook_user_authorization_section( $args ) {
        // Choosing Public is important because:
        // - http://stackoverflow.com/a/19653226/201354
        // - https://github.com/drodenbaugh/awpcp/issues/1288#issuecomment-134198377
        $content  = '<p>' . esc_html__( 'AWPCP needs to get an authorization token from Facebook to work correctly. You\'ll be redirected to Facebook to login. AWPCP does not store or obtain any personal information from your profile.', 'another-wordpress-classifieds-plugin' ) . '</p>';
        $content .= '<p>' . esc_html__( "Please choose Public as the audience for posts made by the application, even if you are just testing the integration. Facebook won't allow us to post content in some cases if you choose something else.", 'another-wordpress-classifieds-plugin' ) . '</p>';

        if ( $this->settings->get_option( 'facebook-app-id' ) && $this->settings->get_option( 'facebook-app-secret' ) ) {
            $facebook = AWPCP_Facebook::instance();

            $redirect_uri         = add_query_arg( 'obtain_user_token', 1, admin_url( '/admin.php?page=awpcp-admin-settings&g=facebook-settings' ) );
            $required_permissions = $facebook->get_required_permissions();
            $login_url            = $facebook->get_login_url( $redirect_uri, implode( ',', $required_permissions ) );

            $content .= '<p><a href="' . $login_url . '">' . __( 'Click here to obtain an access token from Facebook', 'another-wordpress-classifieds-plugin' ) . '</a></p>';
        } else {
            $content .= '<p><strong>' . esc_html__( 'Please provide a value for the App Id and App Secret settings before trying to get an access token from Facebook.', 'another-wordpress-classifieds-plugin' ) . '</strong></p>';
        }

        echo $content; // XSS Ok.
    }

    /**
     * @sicne 3.8.6
     */
    public function facebook_page_and_group_selection_section( $args ) {
        $content  = '<p><strong>' . esc_html__( 'Available Facebook Pages and Groups will be displayed after you enter a valid User Access Token.', 'another-wordpress-classifieds-plugin' ) . '</strong></p>';
        $content .= '<p>' . __( 'As of April 4, 2018, all applications need to go through <a href="https://developers.facebook.com/docs/apps/review" rel="nofollow">App Review</a> in order to get access to the <a href="https://developers.facebook.com/docs/graph-api/reference/page/" rel="nofollow">Page API</a> and <a href="https://developers.facebook.com/docs/graph-api/reference/user/groups/" rel="nofollow">Groups API</a>. That means that you may need to <a href="https://developers.facebook.com/docs/facebook-login/review" rel="nofollow">submit your app for review</a> (ask for the <code>manage_pages</code>, <code>publish_pages</code>, <code>publish_to_groups</code> permissions), before AWPCP can display the list of pages and groups you manage and be able to post classifieds ads to those groups and pages.', 'another-wordpress-classifieds-plugin' ) . '</p>';

        echo $content;
    }

    /**
     * @since 3.8.6
     */
    public function facebook_page_options() {
        $facebook       = AWPCP_Facebook::instance();
        $facebook_pages = $facebook->get_user_pages();

        if ( empty( $facebook_pages ) ) {
            return array();
        }

        $pages         = array(
            'none' => __( 'None (Do not sent ads to a Facebook Page)', 'another-wordpress-classifieds-plugin' ),
        );

        foreach ( $facebook_pages as $page ) {
            $page_name = $page['name'];

            if ( ! empty( $page['profile'] ) ) {
                $page_name = $page_name . ' ' . __( '(Your own profile page)', 'another-wordpress-classifieds-plugin' );
            }

            $pages[ $page['id'] ]         = array(
                'value' => "{$page['id']}|{$page['access_token']}",
                'label' => $page_name,
            );
        }

        return $pages;
    }

    /**
     * @since 3.8.6
     */
    public function facebook_group_options() {
        $facebook        = AWPCP_Facebook::instance();
        $facebook_groups = $facebook->get_user_groups();

        if ( empty( $facebook_groups ) ) {
            return array();
        }

        $groups   = array(
            'none' => __( 'None (Do not sent ads to a Facebook Group)', 'another-wordpress-classifieds-plugin' ),
        );

        foreach ( $facebook_groups as $group ) {
            $groups[ $group['id'] ] = $group['name'];
        }

        return $groups;
    }


    public function validate_group_settings( $options ) {
        if ( ! isset( $options['awpcpadminaccesslevel'] ) ) {
            return $options;
        }

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
	 * General Settings checks
	 */
	public function validate_general_settings( $options ) {
        $this->validate_akismet_settings( $options );
        $this->validate_captcha_settings( $options );

        // Enabling User Ad Management Panel will automatically enable
        // require Registration, if it isn’t enabled. Disabling this feature
        // will not disable Require Registration.
        $setting = 'enable-user-panel';

        if (isset($options[$setting]) && $options[$setting] == 1 && !get_awpcp_option('requireuserregistration')) {
            awpcp_flash(__('Require Registration setting was enabled automatically because you activated the User Ad Management panel.', 'another-wordpress-classifieds-plugin'));
            $options['requireuserregistration'] = 1;
        }

        return $options;
	}

    /**
     * @since 4.0.0
     */
    private function validate_akismet_settings( &$options ) {
        $setting_name = 'use-akismet-in-place-listing-form';
        $akismet_for_place_listing = isset( $options[ $setting_name ] ) && $options[ $setting_name ];

        $setting_name = 'use-akismet-in-reply-to-listing-form';
        $akismet_for_reply_to_listing = isset( $options[ $setting_name ] ) && $options[ $setting_name ];

        if ( $akismet_for_place_listing || $akismet_for_reply_to_listing ) {
            $wpcom_api_key = get_option( 'wordpress_api_key' );
            if ( !function_exists( 'akismet_init' ) ) {
                awpcp_flash( __( 'Akismet SPAM control cannot be enabled because Akismet plugin is not installed or activated.', 'another-wordpress-classifieds-plugin' ), 'error' );
                $options[ 'use-akismet-in-place-listing-form' ] = 0;
                $options[ 'use-akismet-in-reply-to-listing-form' ] = 0;
            } else if ( empty( $wpcom_api_key ) ) {
                awpcp_flash( __( 'Akismet SPAM control cannot be enabled because Akismet is not properly configured.', 'another-wordpress-classifieds-plugin' ), 'error' );
                $options[ 'use-akismet-in-place-listing-form' ] = 0;
                $options[ 'use-akismet-in-reply-to-listing-form' ] = 0;
            }
        }
	}

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function validate_captcha_settings( &$options ) {
        $option_name = 'captcha-enabled-in-place-listing-form';
        $captcha_for_place_listing = isset( $options[ $option_name ] ) && $options[ $option_name ];

        $option_name = 'captcha-enabled-in-reply-to-listing-form';
        $captcha_for_reply_to_listing = isset( $options[ $option_name ] ) && $options[ $option_name ];

        $is_captcha_enabled = $captcha_for_place_listing || $captcha_for_reply_to_listing;

        // Verify reCAPTCHA is properly configured
        if ( $is_captcha_enabled && $options['captcha-provider'] === 'recaptcha' ) {
            if ( empty( $options[ 'recaptcha-public-key' ] ) || empty( $options[ 'recaptcha-private-key' ] ) ) {
                $options['captcha-provider'] = 'math';
            }

            if ( empty( $options[ 'recaptcha-public-key' ] ) && empty( $options[ 'recaptcha-private-key' ] )  ) {
                awpcp_flash( __( "reCAPTCHA can't be used because the public key and private key settings are required for reCAPTCHA to work properly.", 'another-wordpress-classifieds-plugin' ), 'error' );
            } else if ( empty( $options[ 'recaptcha-public-key' ] ) ) {
                awpcp_flash( __( "reCAPTCHA can't be used because the public key setting is required for reCAPTCHA to work properly.", 'another-wordpress-classifieds-plugin' ), 'error' );
            } else if ( empty( $options[ 'recaptcha-private-key' ] ) ){
                awpcp_flash( __( "reCAPTCHA can't be used because the private key setting is required for reCAPTCHA to work properly.", 'another-wordpress-classifieds-plugin' ), 'error' );
            }
        }
	}

    /**
     * @since 4.0.0
     */
    public function validate_date_time_format_settings( $options ) {
        if ( ! isset( $options['x-date-time-format'] ) ) {
            return $options;
        }

        if ( 'custom' === $options['x-date-time-format'] ) {
            return $options;
        }

        $formats = awpcp_get_datetime_formats();

        if ( 'american' === $options['x-date-time-format'] ) {
            $options['date-format']      = $formats['american']['date'];
            $options['time-format']      = $formats['american']['time'];
            $options['date-time-format'] = $formats['american']['format'];
        }

        if ( 'european' === $options['x-date-time-format'] ) {
            $options['date-format']      = $formats['european']['date'];
            $options['time-format']      = $formats['european']['time'];
            $options['date-time-format'] = $formats['european']['format'];
        }

        return $options;
    }

    /**
     * Registration Settings checks
     */
    public function validate_registration_settings( $options ) {
        // if Require Registration is disabled, User Ad Management Panel should be
        // disabled as well.
        $setting = 'requireuserregistration';
        if (isset($options[$setting]) && $options[$setting] == 0 && get_awpcp_option('enable-user-panel')) {
            awpcp_flash(__('User Ad Management panel was automatically deactivated because you disabled Require Registration setting.', 'another-wordpress-classifieds-plugin'));
            $options['enable-user-panel'] = 0;
        }

        if (isset($options[$setting]) && $options[$setting] == 0 && get_awpcp_option('enable-credit-system')) {
            awpcp_flash(__('Credit System was automatically disabled because you disabled Require Registration setting.', 'another-wordpress-classifieds-plugin'));
            $options['enable-credit-system'] = 0;
        }

        return $options;
    }

    /**
     * @since 3.8.6
     */
    public function validate_facebook_settings( $options ) {
        $options['facebook-app-id']             = trim( $options['facebook-app-id'] );
        $options['facebook-app-secret']         = trim( $options['facebook-app-secret'] );
        $options['facebook-user-access-token']  = trim( $options['facebook-user-access-token'] );

        if ( $options['facebook-app-id'] !== $this->settings->get_option( 'facebook-app-id' ) || $options['facebook-app-secret'] !== $this->settings->get_option( 'facebook-app-secret' ) ) {
            $options['facebook-user-access-token'] = '';
            $options['facebook-page']              = '';
            $options['facebook-group']             = '';
            $options['facebook-page-access-token'] = '';
        }

        if ( $options['facebook-user-access-token'] !== $this->settings->get_option( 'facebook-user-access-token' ) ) {
            $options['facebook-page']              = '';
            $options['facebook-group']             = '';
            $options['facebook-page-access-token'] = '';
        }

        if ( ! empty( $options['facebook-page'] ) && 'none' === $options['facebook-page'] ) {
            $options['facebook-page-access-token'] = '';
        }

        if ( ! empty( $options['facebook-page'] ) && strpos( $options['facebook-page'], '|' ) !== false ) {
            $parts = explode( '|', $options['facebook-page'] );

            $options['facebook-page']              = $parts[0];
            $options['facebook-page-access-token'] = $parts[1];
        }

        return $options;
    }
}
