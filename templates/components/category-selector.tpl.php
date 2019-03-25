<?php
/**
 * @package AWPCP\Templates
 */

?><?php if ( $label ) : ?>
<label class="awpcp-category-dropdown-label" for="awpcp-category-dropdown-<?php echo esc_attr( $hash ); ?>"><?php echo esc_html( $label ); ?><?php echo $required ? '<span class="required">*</span>' : ''; // XSS Ok. ?></label>
<?php endif; ?>

<select id="awpcp-category-dropdown-<?php echo esc_attr( $hash ); ?>" class="awpcp-category-dropdown awpcp-dropdown <?php echo $required ? 'required' : ''; ?>" name="<?php echo esc_attr( $name ); ?>" data-hash="<?php echo esc_attr( $hash ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"<?php echo $multiple ? ' multiple="multiple"' : ''; ?><?php echo $auto ? ' data-auto="auto"' : ''; ?>>
    <?php if ( ! $multiple ) : ?>
    <option class="awpcp-dropdown-placeholder"><?php echo esc_html( $placeholder ); ?></option>
    <?php endif; ?>
    <?php echo awpcp_render_categories_dropdown_options( $categories_hierarchy['root'], $categories_hierarchy, $selected ); // XSS Ok. ?>
</select>

<script type="text/javascript">var categories_<?php echo esc_attr( $hash ); ?> = <?php echo wp_json_encode( $javascript ); ?>;</script>
