<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'another-wordpress-classifieds-plugin' ); ?>:</label>
    <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
</p>

<p>
    <input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'hide-empty' ) ); ?>" value="0" />
    <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'hide-empty' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide-empty' ) ); ?>" value="1" <?php echo $instance['hide-empty'] ? 'checked="true"' : ''; ?> />
    <label for="<?php echo esc_attr( $this->get_field_id( 'hide-empty' ) ); ?>"><?php esc_html_e( 'Hide empty categories.', 'another-wordpress-classifieds-plugin' ); ?></label>
    <br/>
    <input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'show-parents-only' ) ); ?>" value="0" />
    <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show-parents-only' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show-parents-only' ) ); ?>" value="1" <?php echo $instance['show-parents-only'] ? 'checked="true"' : ''; ?> />
    <label for="<?php echo esc_attr( $this->get_field_id( 'show-parents-only' ) ); ?>"><?php esc_html_e( 'Show parent categories only.', 'another-wordpress-classifieds-plugin' ); ?></label>
    <br/>
    <input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'show-ad-count' ) ); ?>" value="0" />
    <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show-ad-count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show-ad-count' ) ); ?>" value="1" <?php echo $instance['show-ad-count'] ? 'checked="true"' : ''; ?> />
    <label for="<?php echo esc_attr( $this->get_field_id( 'show-ad-count' ) ); ?>"><?php esc_html_e( 'Show Ad count.', 'another-wordpress-classifieds-plugin' ); ?></label>
</p>
