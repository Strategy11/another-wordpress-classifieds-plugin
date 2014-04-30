<?php if ( $args['label'] ): ?>
<label for="<?php echo $args['id']; ?>"><?php echo $args['label']; ?><?php if ( $args['required'] ): ?><span class="required">*</span><?php endif; ?></label>
<?php endif; ?>
<input type="hidden" name="<?php echo $args['name']; ?>" autocomplete-selected-value>
<input id="<?php echo $args['id']; ?>" class="<?php echo implode( ' ', $args['class'] ); ?>" type="text" autocomplete-field>
