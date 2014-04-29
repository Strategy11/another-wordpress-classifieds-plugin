<label for="ad-user-id"><?php _e( 'User', 'AWPCP' ); ?><span class="required">*</span></label>
<select id="ad-user-id" name="user" class="awpcp-users-dropdown awpcp-dropdown required" dropdown-field>
    <option value=""><?php _e( 'Select an User owner for this Ad', 'AWPCP' ); ?></option>
    <?php foreach ( $users as $k => $user ): ?>
    <option value="<?php echo esc_attr( $user->ID ); ?>" data-user-information="<?php echo esc_attr( json_encode( $user ) ); ?>" <?php echo $selected_user_id == $user->ID ? 'selected="selected"' : ''; ?>>
        <?php echo $user->display_name; ?>
    </option>
    <?php endforeach; ?>
</select>
