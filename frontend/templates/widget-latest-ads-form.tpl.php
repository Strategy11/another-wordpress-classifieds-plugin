<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'another-wordpress-classifieds-plugin' ); ?>:</label>
    <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
</p>

<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Number of Items to Show', 'another-wordpress-classifieds-plugin' ); ?>:</label>
    <input type="text" size="2" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" value="<?php echo esc_attr( $instance['limit'] ); ?>" />
</p>

<p>
    <input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'show-title' ) ); ?>" value="0" />
    <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show-title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show-title' ) ); ?>" value="1" <?php echo $instance['show-title'] ? 'checked="true"' : ''; ?> />
    <label for="<?php echo esc_attr( $this->get_field_id( 'show-title' ) ); ?>"><?php esc_html_e( 'Show Ad title.', 'another-wordpress-classifieds-plugin' ); ?></label>
    <br/>
    <input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'show-excerpt' ) ); ?>" value="0" />
    <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show-excerpt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show-excerpt' ) ); ?>" value="1" <?php echo $instance['show-excerpt'] ? 'checked="true"' : ''; ?> />
    <label for="<?php echo esc_attr( $this->get_field_id( 'show-excerpt' ) ); ?>"><?php esc_html_e( 'Show Ad excerpt.', 'another-wordpress-classifieds-plugin' ); ?></label>
    <br/>
    <input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'show-images' ) ); ?>" value="0" />
    <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show-images' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show-images' ) ); ?>" value="1" <?php echo $instance['show-images'] ? 'checked="true"' : ''; ?> />
    <label for="<?php echo esc_attr( $this->get_field_id( 'show-images' ) ); ?>"><?php esc_html_e( 'Show thumbnails in widget.', 'another-wordpress-classifieds-plugin' ); ?></label>
    <br/>
    <input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'show-blank' ) ); ?>" value="0" />
    <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show-blank' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show-blank' ) ); ?>" value="1" <?php echo $instance['show-blank'] ? 'checked="true"' : ''; ?>/>
    <label for="<?php echo esc_attr( $this->get_field_id( 'show-blank' ) ); ?>"><?php esc_html_e( 'Show "No Image" PNG when Ad has no picture (improves layout).', 'another-wordpress-classifieds-plugin' ); ?></label>
</p>

<p><strong><?php esc_html_e( 'Position of the thumbnail (Desktop):', 'another-wordpress-classifieds-plugin' ); ?></strong></p>

<p>
    <input type="radio" id="<?php echo esc_attr( $this->get_field_id( 'thumbnail-position-in-desktop-above' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumbnail-position-in-desktop' ) ); ?>" value="above" <?php echo $instance['thumbnail-position-in-desktop'] == 'above' ? 'checked="true"' : ''; ?> />
    <label for="<?php echo esc_attr( $this->get_field_id( 'thumbnail-position-in-desktop-above' ) ); ?>"><?php esc_html_e( "Above the Ad's text.", 'another-wordpress-classifieds-plugin' ); ?></label>
    <br/>
    <input type="radio" id="<?php echo esc_attr( $this->get_field_id( 'thumbnail-position-in-desktop-left' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumbnail-position-in-desktop' ) ); ?>" value="left" <?php echo $instance['thumbnail-position-in-desktop'] == 'left' ? 'checked="true"' : ''; ?> />
    <label for="<?php echo esc_attr( $this->get_field_id( 'thumbnail-position-in-desktop-left' ) ); ?>"><?php esc_html_e( "To the left of the Ad's text.", 'another-wordpress-classifieds-plugin' ); ?></label>
    <br/>
    <input type="radio" id="<?php echo esc_attr( $this->get_field_id( 'thumbnail-position-in-desktop-right' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumbnail-position-in-desktop' ) ); ?>" value="right" <?php echo $instance['thumbnail-position-in-desktop'] == 'right' ? 'checked="true"' : ''; ?> />
    <label for="<?php echo esc_attr( $this->get_field_id( 'thumbnail-position-in-desktop-right' ) ); ?>"><?php esc_html_e( "To the right of the Ad's text.", 'another-wordpress-classifieds-plugin' ); ?></label>
</p>

<p><strong><?php esc_html_e( 'Position of the thumbnail (mobile):', 'another-wordpress-classifieds-plugin' ); ?></strong></p>

<p>
    <input type="radio" id="<?php echo esc_attr( $this->get_field_id( 'thumbnail-position-in-mobile-above' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumbnail-position-in-mobile' ) ); ?>" value="above" <?php echo $instance['thumbnail-position-in-mobile'] == 'above' ? 'checked="true"' : ''; ?> />
    <label for="<?php echo esc_attr( $this->get_field_id( 'thumbnail-position-in-mobile-above' ) ); ?>"><?php esc_html_e( "Above the Ad's text.", 'another-wordpress-classifieds-plugin' ); ?></label>
    <br/>
    <input type="radio" id="<?php echo esc_attr( $this->get_field_id( 'thumbnail-position-in-mobile-left' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumbnail-position-in-mobile' ) ); ?>" value="left" <?php echo $instance['thumbnail-position-in-mobile'] == 'left' ? 'checked="true"' : ''; ?> />
    <label for="<?php echo esc_attr( $this->get_field_id( 'thumbnail-position-in-mobile-left' ) ); ?>"><?php esc_html_e( "To the left of the Ad's text.", 'another-wordpress-classifieds-plugin' ); ?></label>
    <br/>
    <input type="radio" id="<?php echo esc_attr( $this->get_field_id( 'thumbnail-position-in-mobile-right' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumbnail-position-in-mobile' ) ); ?>" value="right" <?php echo $instance['thumbnail-position-in-mobile'] == 'right' ? 'checked="true"' : ''; ?> />
    <label for="<?php echo esc_attr( $this->get_field_id( 'thumbnail-position-in-mobile-right' ) ); ?>"><?php esc_html_e( "To the right of the Ad's text.", 'another-wordpress-classifieds-plugin' ); ?></label>
</p>

