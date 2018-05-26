<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Exporter for User personal data.
 */
class AWPCP_UserPersonalDataExporter implements AWPCP_PersonalDataExporterInterface {

    /**
     * @var
     */
    private $data_formatter;

    /**
     * @since 3.8.6
     */
    public function __construct( $data_formatter ) {
        $this->data_formatter = $data_formatter;
    }

    /**
     * @since 3.8.6
     */
    public function get_page_size() {
        return 10;
    }

    /**
     * @since 3.8.6
     */
    public function get_objects( $user, $email_address, $page ) {
        if ( ! is_object( $user ) ) {
            return array();
        }

        $metadata = get_user_meta( $user->ID, 'awpcp-profile', true );

        if ( ! $metadata ) {
            return array();
        }

        $user->classifieds_profile = $metadata;

        return array( $user );
    }

    /**
     * @since 3.8.6
     */
    public function export_objects( $users ) {
        $items = array(
            'address' => __( 'Contact Address', 'another-wordpress-classifieds-plugin' ),
            'phone'   => __( 'Contact Phone', 'another-wordpress-classifieds-plugin' ),
            'country' => __( 'Default Country', 'another-wordpress-classifieds-plugin' ),
            'state'   => __( 'Default State', 'another-wordpress-classifieds-plugin' ),
            'city'    => __( 'Default City', 'another-wordpress-classifieds-plugin' ),
            'county'  => __( 'Default County', 'another-wordpress-classifieds-plugin' ),
        );

        $export_items = array();

        foreach ( $users as $user ) {
            $data = $this->data_formatter->format_data( $items, $user->classifieds_profile );

            $export_items[] = array(
                'group_id'    => 'awpcp-profile',
                'group_label' => __( 'Classifieds Profile', 'another-wordpress-classifieds-plugin' ),
                'item_id'     => "user-{$user->ID}",
                'data'        => $data,
            );
        }

        return $export_items;
    }
}
