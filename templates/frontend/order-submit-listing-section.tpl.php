<?php
/**
 * @package AWPCP\Templates\Frontend
 */

?><div class="awpcp-order-submit-listing-section awpcp-submit-listing-section">
    <h2 class="awpcp-submit-listing-section-title js-handler"><?php echo esc_html_x( 'Category, owner and payment term selection', 'order submit listing section', 'another-wordpress-classifieds-plugin' ); ?><span></span></h2>

    <div class="awpcp-submit-listing-section-content" data-collapsible awpcp-keep-open>
        <div class="awpcp-order-submit-listing-section__edit_mode">
            <form>
                <input type="hidden" name="listing_id" value="<?php echo esc_attr( $form['listing_id'] ); ?>"/>

                <div class="awpcp-form-spacer">
                    <?php
                        $params = array(
                            'name'          => 'category',
                            'label'         => _x( 'Please select a category for your classified', 'order submit listing section', 'another-wordpress-classifieds-plugin' ),
                            'selected'      => $form['category'],
                            'multiple'      => false,
                            'auto'          => false,
                            'hide_empty'    => false,
                            'payment_terms' => $payment_terms,
                        );

                        $params = apply_filters( 'awpcp_post_listing_categories_selector_args', $params );

                        echo awpcp_categories_selector()->render( $params ); // XSS Ok.
                        echo awpcp_form_error( 'category', $form_errors ); // XSS Ok.
                    ?>
                </div>

                <?php if ( $show_user_field ) : ?>
                <div class="awpcp-form-spacer">
                    <?php
                        echo awpcp()->container['UserSelector']->render( array(
                            'required' => true,
                            'selected' => awpcp_array_data( 'user', '', $form ),
                            'label'    => _x( 'Who is the owner of this classified?', 'order submit listing section', 'another-wordpress-classifieds-plugin' ),
                            'default'  => __( 'Please select a user', 'another-wordpress-classifieds-plugin' ),
                            'id'       => 'ad-user-id',
                            'name'     => 'user',
                            'class'    => array( 'awpcp-user-selector' ),
                        ) ); // XSS Ok.
                    ?>
                    <?php echo awpcp_form_error( 'user', $form_errors ); // XSS Ok. ?>
                </div>
                <?php endif; ?>

                <div class="awpcp-form-spacer">
                    <label><?php echo esc_html_x( 'Please select the duration and features that will be available for this classified', 'order submit listing section', 'another-wordpress-classifieds-plugin' ); ?><span class="required">*</span></label>

                    <?php if ( $show_account_balance ) : ?>
                    <?php echo $account_balance; // XSS Ok. ?>
                    <?php endif; ?>

                    <?php echo $payment_terms_list; // XSS Ok. ?>
                    <?php echo $credit_plans_table; // XSS Ok. ?>
                </div>

                <?php if ( $show_captcha ) : ?>
                <div class='awpcp-form-spacer'>
                    <?php echo $captcha->render(); // XSS Ok. ?>
                    <?php echo awpcp_form_error( 'captcha', $form_errors ); // XSS Ok. ?>
                </div>
                <?php endif; ?>

                <p class="form-submit">
                    <input class="awpcp-order-submit-listing-section--continue-button button button-primary" type="submit" value="<?php echo esc_attr_x( 'Continue', 'order submit listing section', 'another-wordpress-clasifieds-plugin' ); ?>"/>
                </p>
            </form>
        </div>

        <div class="awpcp-order-submit-listing-section__read_mode">
            <p class="awpcp-order-submit-listing-section--selected-categories-container"><?php echo str_replace( '{categories}', '<span class="awpcp-order-submit-listing-section--selected-categories"></span>', esc_html_x( 'Your classified will be posted on the following categories: {categories}.', 'order submit listing section', 'another-wordpress-classifieds-plugin' ) ); // XSS Ok. ?></p>
            <div class="awpcp-order-submit-listing-section--payment-term awpcp-payment-term awpcp-payment-term__read_only"></div>
            <p class="awpcp-order-submit-listing-section--credit-plan"><?php echo esc_html_x( 'Credit Plan:', 'order submit listing section', 'another-wordpress-classifieds-plugin' ); ?> <span></span></p>
            <p class="awpcp-order-submit-listing-section--listing-owner"><?php echo esc_html_x( 'Owner:', 'order submit listing section', 'another-wordpress-classifieds-plugin' ); ?> <span></span></p>

            <p class="form-submit">
            <span class="awpcp-order-submit-listing-section--loading-message"><?php echo esc_html_x( 'Loading classified fields', 'order submit listing section', 'another-wordpress-classifieds-plugin' ); ?><span class="awpcp-spinner"></span></span>
                <input class="awpcp-order-submit-listing-section--change-selection-button button button-primary" type="submit" value="<?php echo esc_attr_x( 'Change selection', 'order submit listing section', 'another-wordpress-clasifieds-plugin' ); ?>"/>
            </p>
        </div>
    </div>

    <script type="text/javascript">
    /* <![CDATA[ */
        window.awpcp = window.awpcp || {};
        window.awpcp.options = window.awpcp.options || [];
        window.awpcp.options.push( ['create_empty_listing_nonce', <?php echo wp_json_encode( $nonce ); ?> ] );
    /* ]]> */
    </script>
</div>
