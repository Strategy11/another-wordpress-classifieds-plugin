<?php
/**
 * @package AWPCP\Templates\Admin\Import
 */

?>
<?php $page_id = 'awpcp-tools'; ?>
<?php $page_title = awpcp_admin_page_title( __( 'Tools', 'another-wordpress-classifieds-plugin' ) ); ?>

<?php require AWPCP_DIR . '/admin/templates/admin-panel-header.tpl.php'; ?>

            <ul class="ul-disc">
                <?php $import_and_export_url = add_query_arg( 'awpcp-view', 'import-settings' ); ?>
                <li>
                    <strong><a href="<?php echo esc_url( $import_and_export_url ); ?>"><?php esc_html_e( 'Import and Export', 'another-wordpress-classifieds-plugin' ); ?></a></strong>
                    <br>
                    <?php esc_html_e( 'Import and export your settings for re-use on another site.', 'another-wordpress-classifieds-plugin' ); ?>
                </li>
            </ul>

        </div><!-- end of .awpcp-main-content -->
    </div><!-- end of .page-content -->
</div><!-- end of #page_id -->
