<?php
/**
 * @package AWPCP\Templates
 */

?><?php if ( $label && $required ) : ?>
<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?><span class="required">*</span></label>
<?php elseif ( $label ) : ?>
<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
<?php endif; ?>

<?php // TODO: Remove style attribute. ?>
<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( implode( ' ', $class ) ); ?>" data-configuration="<?php echo esc_attr( wp_json_encode( $configuration ) ); ?>" style="width: 100%">
    <?php if ( $default ) : ?>
    <option value=""><?php echo esc_html( $default ); ?></option>
    <?php endif; ?>

    <?php foreach ( $users as $k => $user ) : ?>

    <?php if ( $include_full_user_information ) : ?>
    <option value="<?php echo esc_attr( $user->ID ); ?>" data-user-information='<?php echo wp_json_encode( $user ); ?>'<?php echo $selected && $selected['id'] === $user->ID ? ' selected="selected"' : ''; ?>>
    <?php else : ?>
    <option value="<?php echo esc_attr( $user->ID ); ?>"<?php echo $selected && $selected['id'] === $user->ID ? ' selected="selected"' : ''; ?>>
    <?php endif; ?>
        <?php echo esc_html( $user->public_name ); ?>
    </option>

    <?php endforeach; ?>
</select>
