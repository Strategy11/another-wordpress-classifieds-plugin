<p class="awpcp-form-spacer">
    <?php $validator = $required ? 'required url' : 'url' ?>
    <label for="<?php echo esc_attr( $html['id'] ); ?>"><?php echo esc_html( $label ); ?><?php echo $required ? '*' : ''; ?></label>
    <input class="inputbox required" id="<?php echo esc_attr( $html['id'] ); ?>" type="text" size="50" name="<?php echo esc_attr( $html['name'] ); ?>" value="<?php echo awpcp_esc_attr( $value ); ?>" />
    <?php echo awpcp_form_error( $html['name'], $errors ); ?>
</p>
