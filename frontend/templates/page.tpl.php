<?php awpcp_print_messages() ?>

<div class="<?php echo $this->page ?> awpcp-page" id="classiwrapper">

    <?php if ( $this->show_menu_items ): ?>
        <?php echo awpcp_menu_items(); ?>
    <?php endif; ?>

	<?php echo $content ?>
</div>
