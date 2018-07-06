<?php
/**
 * @package AWPCP\Settings
 */

/**
 * Handles integration between plugin settings and WordPress Settings API.
 */
class AWPCP_SettingsIntegration {

    /**
     * @var string
     */
    private $page_hook;

    /**
     * @var SettingsManager
     */
    private $settings_manager;

    /**
     * @var SettingsValidator
     */
    private $settings_validator;

    /**
     * @var SettingsRenderer
     */
    private $settings_renderer;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @since 4.0.0
     */
    public function __construct( $page_hook, $settings_manager, $settings_validator, $settings_renderer, $settings ) {
        $this->page_hook          = $page_hook;
        $this->settings_manager   = $settings_manager;
        $this->settings_validator = $settings_validator;
        $this->settings_renderer  = $settings_renderer;
        $this->settings           = $settings;
    }

    /**
     * @since 4.0.0
     */
    public function setup() {
        register_setting(
            $this->settings->setting_name,
            $this->settings->setting_name,
            [
                // TODO: This should probable be handled elsewhere.
                'sanitize_callback' => [ $this->settings_validator, 'sanitize_settings' ],
            ]
        );

        add_action( $this->page_hook, [ $this, 'add_settings_sections' ] );
    }

    /**
     * @since 4.0.0
     */
    public function add_settings_sections() {
        foreach ( $this->settings_manager->get_settings_groups() as $group ) {
            $this->add_settings_sections_for_group( $group );
        }
    }

    /**
     * @since 4.0.0
     */
    private function add_settings_sections_for_group( $group ) {
        $subgroups = $this->settings_manager->get_settings_subgroups();

        foreach ( $group['subgroups'] as $subgroup_id ) {
            $this->add_settings_sections_for_subgroup( $subgroups[ $subgroup_id ] );
        }
    }

    /**
     * @since 4.0.0
     */
    private function add_settings_sections_for_subgroup( $subgroup ) {
        $sections = $this->settings_manager->get_settings_sections();

        // $sections is sorted by priority, $subgroup['sections'] is not.
        $sections_ids = array_intersect( array_keys( $sections ), $subgroup['sections'] );

        foreach ( $sections_ids as $section_id ) {
            $this->add_settings_sections_for_section( $sections[ $section_id ] );
        }
    }

    /**
     * @since 4.0.0
     */
    private function add_settings_sections_for_section( $section ) {
        add_settings_section(
            $section['id'],
            $section['name'],
            [ $this->settings_renderer, 'render_settings_section' ],
            $section['subgroup']
        );

        foreach ( $section['settings'] as $setting_id ) {
            $setting = $this->settings_manager->get_setting( $setting_id );

            add_settings_field(
                $setting['id'],
                $setting['name'],
                [ $this->settings_renderer, 'render_setting' ],
                $section['subgroup'],
                $section['id'],
                [
                    'setting_id' => $setting_id,
                ]
            );
        }
    }
}
