<?php $page_id = 'awpcp-admin-csv-importer' ?>
<?php $page_title = awpcp_admin_page_title( __( 'Import Listings', 'another-wordpress-classifieds-plugin' ) ); ?>

<?php include( AWPCP_DIR . '/admin/templates/admin-panel-header.tpl.php') ?>

        <h3><?php echo esc_html( __( 'Import', 'another-wordpress-classifieds-plugin' ) ); ?></h3>

        <form id="awpcp-import-listings-import-form" method="post">
            <div class="progress-bar">
                <div class="progress-bar-value" data-bind="progress: progress"></div>
            </div>

            <p data-bind="html: progressReport"></p>

            <table class="awpcp-table">
                <thead>
                    <tr>
                        <th><?php echo __( 'Line No.', 'another-wordpress-classifieds-plugin' ); ?></th>
                        <th><?php echo __( 'Error Message', 'another-wordpress-classifieds-plugin' ); ?></th>
                    </tr>
                </thead>
                <tbody data-bind="foreach: errors">
                    <tr>
                        <td data-bind="text: line"></td>
                        <td data-bind="html: message"></td>
                    </tr>
                </tbody>
            </table>

            <p class="submit">
                <input type="submit" class="button" name="change_configuration" value="<?php echo esc_html( __( 'Change Configuration & Restart', 'another-wordpress-classifieds-plugin' ) ); ?>" data-bind="visible: completed"></input>
                <input type="submit" class="button-primary button" name="start" value="<?php echo esc_html( __( 'Start Import', 'another-wordpress-classifieds-plugin' ) ); ?>" data-bind="click: start"></input>
            </p>

            <hr>

            <p><?php echo __( "Press the button below to cancel the current import operation and discard the uploaded CSV file and ZIP file (if any). If you manually uploaded images to the directory specified in the Local Directory field, those won't be deleted.", 'another-wordpress-classifieds-plugin' ); ?></p>

            <p class="cancel-submit">
                <input type="submit" class="button" name="cancel" value="<?php echo esc_html( __( 'Cancel', 'another-wordpress-classifieds-plugin' ) ); ?>"></input>
            </p>
        </form>

        </div><!-- end of .awpcp-main-content -->
    </div><!-- end of .page-content -->
</div><!-- end of #page_id -->
