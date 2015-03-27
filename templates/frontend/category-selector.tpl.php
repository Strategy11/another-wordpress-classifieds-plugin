<div class="changecategoryselect">
    <form method="post" action="<?php echo esc_attr( $browse_categories_page_url ); ?>">
        <div class="awpcp-category-dropdown-container">
        <?php $dropdown = new AWPCP_CategoriesDropdown(); ?>
        <?php echo $dropdown->render( $category_dropdown_params ); ?>
        </div>

        <input type="hidden" name="a" value="browsecat" />
        <input class="button" type="submit" value="<?php echo esc_attr( __( 'Change Category', 'AWPCP' ) ); ?>" />
    </form>

    <?php if ( $category_id > 0 ): ?>
    <div id='awpcpcatname' class="fixfloat">
        <h3><?php echo esc_html( __( 'Category: ', 'AWPCP' ) . get_adcatname( $category_id ) ); ?></h3>
    </div>
    <?php endif; ?>
</div>
