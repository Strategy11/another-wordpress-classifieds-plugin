<div id="classiwrapper">
    <?php echo $before_content; ?>

    <?php if ( $options['show_intro_message'] ): ?>
    <div class="uiwelcome"><?php echo stripslashes_deep( get_awpcp_option( 'uiwelcome' ) ); ?></div>
    <?php endif; ?>

    <?php if ( $options['show_menu_items'] ): ?>
    <?php echo awpcp_menu_items(); ?>
    <?php endif; ?>

    <?php if ( $options['show_category_selector'] ): ?>

    <?php
        $awpcp_browsecats_pageid = awpcp_get_page_id_by_ref( 'browse-categories-page-name' );
        $url_browsecatselect = get_permalink( $awpcp_browsecats_pageid );

        $category_id = (int) awpcp_request_param('category_id', -1);
        $category_id = $category_id === -1 ? (int) get_query_var('cid') : $category_id;
    ?>

    <div class="changecategoryselect">
        <form method="post" action="<?php echo esc_attr( $url_browsecatselect ); ?>">
            <div class="awpcp-category-dropdown-container">
            <?php $dropdown = new AWPCP_CategoriesDropdown(); ?>
            <?php echo $dropdown->render( array( 'context' => 'search', 'name' => 'category_id', 'selected' => $category_id ) ); ?>
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
    <?php endif; ?>

    <?php echo implode( '', $before_pagination ); ?>
    <?php echo $pagination; ?>
    <?php echo $before_list; ?>

    <div class="awpcp-listings clearboth">
        <?php if ( count( $items ) ): ?>
            <?php echo implode( '', $items ); ?>
        <?php else: ?>
            <p><?php echo esc_html( __( 'There were no listings found.', 'AWPCP' ) ); ?></p>
        <?php endif;?>
    </div>

    <?php echo $pagination; ?>
    <?php echo implode( '', $after_pagination ); ?>
    <?php echo $after_content; ?>
</div>
