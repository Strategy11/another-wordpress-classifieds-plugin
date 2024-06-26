<h3><?php echo esc_html( __( 'Classifieds Contact Information', 'another-wordpress-classifieds-plugin' ) ); ?></h3>

<table class="form-table">
    <tbody>
        <tr valign="top">
            <th scope="row">
                <label for="awpcp-profile-phone"><?php esc_html_e( 'Phone Number', 'another-wordpress-classifieds-plugin' ); ?></label>
            </th>
            <td>
                <input id="awpcp-profile-phone" class="regular-text" type="text" name="awpcp-profile[phone]" value="<?php echo esc_attr(awpcp_array_data('phone', '', $profile)) ?>" />
                <span class="description"></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="awpcp-profile-address"><?php esc_html_e( 'Address', 'another-wordpress-classifieds-plugin' ); ?></label>
            </th>
            <td>
                <input id="awpcp-profile-address" class="regular-text" type="text" name="awpcp-profile[address]" value="<?php echo esc_attr(awpcp_array_data('address', '', $profile)) ?>" />
                <span class="description"></span>
            </td>
        </tr>
    </tbody>
</table>

<?php
$selector_options = array(
    'showTextField' => true,
    'maxRegions' => 1,
);

$selected_region = array(
    array(
        'country' => awpcp_array_data( 'country', '', $profile ),
        'state' => awpcp_array_data( 'state', '', $profile ),
        'city' => awpcp_array_data( 'city', '', $profile ),
        'county' => awpcp_array_data( 'county', '', $profile ),
    ),
);

$selector = awpcp_multiple_region_selector_with_template( $selected_region, $selector_options, 'form-table' );
$selector->show( 'user-profile', array(), array() );
