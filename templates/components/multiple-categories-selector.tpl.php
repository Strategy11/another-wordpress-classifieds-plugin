<div class="awpcp-multiple-categories-selector-container">
    <?php if ( $label ): ?>
    <label class="awpcp-multiple-categories-selector-label"><?php echo esc_html( $label ); ?><?php echo $required ? '<span class="required">*</span>' : ''; ?></label>
    <?php endif; ?>
    <div class="awpcp-multiple-categories-selector" data-multiple-value-selector-id="<?php echo esc_attr( $unique_id ); ?>">
        <div class="awpcp-categories-selector-categories-lists"></div>
    </div>
</div>
