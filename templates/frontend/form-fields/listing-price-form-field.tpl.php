<div class="awpcp-form-spacer">
    <label for="<?php echo esc_attr( $html['id'] ); ?>"><?php echo esc_html( $label ); ?><?php echo $required ? '*' : ''; ?><?php if ( ! empty( $help_text ) ): ?>&nbsp;<span class="helptext"><?php echo esc_html( $help_text ); ?></span><?php endif; ?></label>
    <?php if ( $show_currency_symbol_on_right ): ?>
    <label><input class="awpcp-textfield awpcp-price-textfield inputbox <?php echo esc_attr( $validators ); ?>" id="<?php echo esc_attr( $html['id'] ); ?>" <?php echo $html['readonly'] ? 'readonly="readonly"' : ''; ?> type="text" size="50" name="<?php echo esc_attr( $html['name'] ); ?>" value="<?php echo awpcp_esc_attr( $value ); ?>" /><span class="awpcp-price-form-field-curency-symbol awpcp-price-form-field-curency-symbol-on-right"><?php echo $currency_symbol; ?></span></label>
    <?php else: ?>
    <label><span class="awpcp-price-form-field-curency-symbol awpcp-price-form-field-curency-symbol-on-left"><?php echo $currency_symbol; ?></span><input class="awpcp-textfield awpcp-price-textfield inputbox <?php echo esc_attr( $validators ); ?>" id="<?php echo esc_attr( $html['id'] ); ?>" <?php echo $html['readonly'] ? 'readonly="readonly"' : ''; ?> type="text" size="50" name="<?php echo esc_attr( $html['name'] ); ?>" value="<?php echo awpcp_esc_attr( $value ); ?>" /></label>
    <?php endif; ?>
    <?php echo awpcp_form_error( $html['name'], $errors ); ?>
</div>
