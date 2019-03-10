<?php
/**
 * @package AWPCP\Templates\Admin\Import
 */

?>
<?php $page_id = 'awpcp-tools'; ?>
<?php $page_title = awpcp_admin_page_title( __( 'Tools', 'another-wordpress-classifieds-plugin' ) ); ?>

<?php require AWPCP_DIR . '/admin/templates/admin-panel-header.tpl.php'; ?>

<ul class="ul-disc">
    <?php foreach ( $params as $view ) : ?>
        <?php $import_and_export_url = add_query_arg( 'awpcp-view', 'import-settings' ); ?>
        <li>
            <?php
            // Already escaped.
            // @phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <strong><a href="<?php echo $view['url']; ?>"><?php echo $view['title']; ?></a></strong>
            <br>
            <?php // @phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php echo $view['description']; ?>
        </li>
    <?php endforeach; ?>
</ul>

</div><!-- end of .awpcp-main-content -->
</div><!-- end of .page-content -->
</div><!-- end of #page_id -->
