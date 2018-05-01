<?php if ( $label ): ?>
<label class="awpcp-category-dropdown-label" for="awpcp-category-dropdown"><?php echo $label ?><?php echo $required ? '<span class="required">*</span>' : ''; ?></label>
<?php endif; ?>

<?php $hash = uniqid(); ?>

<select class="awpcp-category-dropdown  awpcp-dropdown <?php echo $required ? 'required' : ''; ?>" id="awpcp-category-dropdown" name="<?php echo esc_attr( $name ); ?>">
    <option value=""><?php echo esc_html( $placeholders['default-option-first-level'] ); ?></option>
    <?php echo awpcp_render_categories_dropdown_options( $categories_hierarchy['root'], $categories_hierarchy, $selected ); ?>
</select>

<script type="text/javascript">var categories_<?php echo $hash; ?> = <?php echo json_encode( $categories_hierarchy ); ?>;</script>
