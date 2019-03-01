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
        <strong><a href="<?php echo esc_url( $import_and_export_url ); ?>"><?php esc_html_e( 'Import and Export Settings', 'another-wordpress-classifieds-plugin' ); ?></a></strong>
        <br>
		<?php esc_html_e( 'Import and export your settings for re-use on another site.', 'another-wordpress-classifieds-plugin' ); ?>
    </li>

	<?php $import_listings_url = add_query_arg( 'awpcp-view', 'awpcp-import' ); ?>
    <li>
        <strong><a href="<?php echo esc_url( $import_listings_url ); ?>"><?php esc_html_e( 'Import Listings', 'another-wordpress-classifieds-plugin' ); ?></a></strong>
    </li>

	<?php
	// lets make sure the awpcp-admin-import-zip-code-database is registered
	global $awpcp;
	$admin_pages           = $awpcp->router->routes->get_admin_pages();
	$awpcp_zip_code_import = $admin_pages['awpcp.php']->subpages['awpcp-tools']->sections['awpcp-admin-import-zip-code-database'];
	if ( $awpcp_zip_code_import ) :
		$import_zip_code_url = add_query_arg( 'awpcp-view', 'awpcp-admin-import-zip-code-database' );
		?>
        <li>
            <strong><a href="<?php echo esc_url( $import_zip_code_url ); ?>"><?php esc_html_e( 'Import ZIP Code Database', 'another-wordpress-classifieds-plugin' ); ?></a></strong>
        </li>
	<?php endif; ?>
</ul>

</div><!-- end of .awpcp-main-content -->
</div><!-- end of .page-content -->
</div><!-- end of #page_id -->
