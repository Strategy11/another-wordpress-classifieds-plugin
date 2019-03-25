<div class="awpcp-manage-categories-category-form-container postbox-container">
    <div class="metabox-holder">
        <div class="metabox-sortables">
            <div class="postbox">
                <?php
                    echo awpcp_html_admin_third_level_heading(array(
                        'content' => $form_title,
                        'attributes' => array( 'class' => 'hndle' ),
                    ));
                ?>
                <div class="inside">
                    <?php if ( $category_has_listings && $category_has_children ): ?>
                    <p><?php echo __( "The category has associated listings and children categories. Please select a category to move the listings to and to set as the new parent of the children categories.", 'another-wordpress-classifieds-plugin' ); ?></p>
                    <?php elseif ( $category_has_listings ): ?>
                    <p><?php echo __( "The category has associated listings. Please select a category to move the listings to.", 'another-wordpress-classifieds-plugin' ); ?></p>
                    <?php elseif ( $category_has_children ): ?>
                    <p><?php echo __( "The category has children categories. Please select a category to set as the new parent of the children categories.", 'another-wordpress-classifieds-plugin' ); ?></p>
                    <?php endif; ?>

                    <p><?php
                        $message = __( 'Click <cancel-button-label> to go back to the list of categories or click <submit-button-label> to proceed.', 'another-wordpress-classifieds-plugin' );
                        $message = str_replace( '<cancel-button-label>', '<strong>' . $form_cancel . '</strong>', $message );
                        $message = str_replace( '<submit-button-label>', '<strong>' . $form_submit . '</strong>', $message );

                        echo $message;
                    ?></p>

                    <form id="awpcp_launch" class="awpcp-delete-categories-category-form" method="post">
                        <input type="hidden" name="awpcp-action" value="<?php echo $form_values['action']; ?>" />
                        <input type="hidden" name="category_id" value="<?php echo $form_values['category_id']; ?>" />
                        <input type="hidden" name="offset" value="<?php echo $offset; ?>" />
                        <input type="hidden" name="results" value="<?php echo $results; ?>" />

                        <?php if ( $category_has_children || $category_has_listings ): ?>
                        <div class="awpcp-clearfix clearfix">
                            <div class="awpcp-manage-categories-category-form-field">
                                <label for="awpcp-category-parent-field"><?php echo __( 'Selected Category', 'another-wordpress-classifieds-plugin' ); ?></label>
                                <select id="awpcp-category-parent-field" name="target_category_id">
                                    <?php echo get_categorynameid( $form_values['category_id'], $form_values['target_category_id'] ); ?>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>

                        <p class="submit inline-edit-save">
                            <input type="submit" class="button" name="awpcp-cancel-delete-category" value="<?php echo esc_attr( $form_cancel ); ?>" />
                            <input type="submit" class="button-primary button" name="awpcp-confirm-delete-category" value="<?php echo esc_attr( $form_submit ); ?>" />
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
