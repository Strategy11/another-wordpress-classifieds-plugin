<tr style="" class="inline-edit-row quick-edit-row alternate inline-editor" id="edit-1">
    <td class="colspanchange" colspan="5">
        <?php $id = awpcp_get_property( $entry, 'id', false ); ?>
        <form action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" method="post">
        <fieldset class="inline-edit-col-left"><div class="inline-edit-col">
                <h4><?php echo esc_html( $id ? _x( 'Edit Credit Plan Details', 'credit plans form', 'another-wordpress-classifieds-plugin' ) : _x( 'New Credit Plan Details', 'credit plans form', 'another-wordpress-classifieds-plugin' ) ); ?></h4>

                <label>
                    <span class="title"><?php echo esc_html( __( 'Name', 'another-wordpress-classifieds-plugin' ) ); ?></span>
                    <span class="input-text-wrap"><input type="text" value="<?php echo esc_attr( awpcp_get_property( $entry, 'name' ) ); ?>" name="name"></span>
                </label>

                <label>
                    <span class="title"><?php echo esc_html( __( 'Credits', 'another-wordpress-classifieds-plugin' ) ); ?></span>
                    <span class="input-text-wrap formatted-field"><input type="text" value="<?php echo esc_attr( $entry ? $entry->credits : '' ); ?>" name="credits"></span>
                </label>

                <label>
                    <span class="title"><?php echo esc_html( __( 'Price', 'another-wordpress-classifieds-plugin' ) ); ?></span>
                    <span class="input-text-wrap formatted-field"><input type="text" value="<?php echo esc_attr( $entry ? $entry->price : '' ); ?>" name="price"></span>
                </label>
        </fieldset>

        <fieldset class="inline-edit-col-right"><div class="inline-edit-col">
                <label><span class="title"><?php echo esc_html( __( 'Description', 'another-wordpress-classifieds-plugin' ) ); ?></span></label>
                <textarea name="description" cols="54" rows="6"><?php echo esc_textarea( stripslashes( awpcp_get_property( $entry, 'description' ) ) ); ?></textarea>
        </fieldset>

        <p class="submit inline-edit-save">
            <?php $label = $id ? __( 'Update', 'another-wordpress-classifieds-plugin') : __( 'Add', 'another-wordpress-classifieds-plugin') ?>
            <?php $cancel = __( 'Cancel', 'another-wordpress-classifieds-plugin'); ?>
            <a class="button-secondary cancel alignleft" title="<?php echo esc_attr( $cancel ); ?>" href="#inline-edit" accesskey="c"><?php echo esc_html( $cancel ); ?></a>
            <a class="button-primary save alignright" title="<?php echo esc_attr( $label ); ?>" href="#inline-edit" accesskey="s"><?php echo esc_html( $label ); ?></a>
            <img alt="" src="<?php echo esc_attr( admin_url( '/images/wpspin_light.gif' ) ); ?>" style="display: none;" class="waiting">
            <input type="hidden" value="<?php echo esc_attr( $id ) ?>" name="id">
            <input type="hidden" value="<?php echo esc_attr( awpcp_get_var( array( 'param' => 'action' ), 'post' ) ); ?>" name="action" />
            <br class="clear">
        </p>
        </form>
    </td>
</tr>
