<form method="post" action="<?php echo esc_attr($this->url(array('action' => false))) ?>">
    <?php /*foreach ($this->params as $name => $value): ?>
    <input type="hidden" name="<?php echo esc_attr($name) ?>" value="<?php echo esc_attr($value) ?>" />
    <?php endforeach*/ ?>

    <?php echo $table->views(); ?>
    <?php echo $table->display(); ?>
</form>
