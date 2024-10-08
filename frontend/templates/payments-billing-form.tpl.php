<p><?php esc_html_e( 'Please fill in the billing information in the form below to place your payment.', 'another-wordpress-classifieds-plugin' ); ?></p>

<form class="awpcp-billing-form" method="post">
    <fieldset>
        <div class="awpcp-form-spacer clearfix">
            <label for="awpcp-billing-country"><?php esc_html_e( 'Country', 'another-wordpress-classifieds-plugin' ); ?></label>
            <select id="awpcp-billling-country" class="required" name="country" data-bind="value: country">
                <?php awpcp_country_list_options( awpcp_array_data( 'country', '', $data ), false, 'echo' ); ?>
            </select>
            <?php awpcp_show_form_error( 'country', $errors ); ?>
        </div>

        <div class="awpcp-form-spacer clearfix">
            <label for="awpcp-billing-credit-card-number"><?php esc_html_e( 'Card Number', 'another-wordpress-classifieds-plugin' ); ?></label>
            <input class="textfield required" id="awpcp-billing-credit-card-number" type="text" size="50" name="credit_card_number" value="<?php echo esc_attr( awpcp_array_data( 'credit_card_number', '', $data ) ); ?>" data-bind="value: credit_card_number">
            <?php awpcp_show_form_error( 'credit_card_number', $errors ); ?>
        </div>

        <div class="awpcp-form-spacer clearfix">
            <label for="awpcp-billing-card-type"><?php esc_html_e( 'Card Type', 'another-wordpress-classifieds-plugin' ); ?></label>
            <div class="awpcp-billing-credit-card-type">
                <label for="awpcp-billing-credit-card-type-visa">
                    <input id="awpcp-billing-credit-card-type-visa" type="radio" name="credit_card_type" value="Visa" tabindex="-1" data-bind="checked: credit_card_type">
                    <span class="cclogo visa" data-bind="css: { fade: hide_visa }">&nbsp;</span>
                    <span>Visa</span>
                </label>
                <label for="awpcp-billing-credit-card-type-mastercard">
                    <input class="mc_subtype" id="awpcp-billing-credit-card-type-mastercard" type="radio" name="credit_card_type" value="MasterCard" tabindex="-1" data-bind="checked: credit_card_type">
                    <span class="cclogo mastercard" data-bind="css: { fade: hide_mastercard }">&nbsp;</span>
                    <span>MasterCard</span>
                </label>
                <label for="awpcp-billing-credit-card-type-discover">
                    <input id="awpcp-billing-credit-card-type-discover" type="radio" name="credit_card_type" value="Discover" tabindex="-1" data-bind="checked: credit_card_type">
                    <span class="cclogo discover" data-bind="css: { fade: hide_discover }">&nbsp;</span>
                    <span>Discover</span>
                </label>
                <label for="awpcp-billing-credit-card-type-amex">
                    <input id="awpcp-billing-credit-card-type-amex" type="radio" name="credit_card_type" value="Amex" tabindex="-1" data-bind="checked: credit_card_type">
                    <span class="cclogo amex" data-bind="css: { fade: hide_amex }">&nbsp;</span>
                    <span>American Express</span>
                </label>
            </div>
        </div>

        <div class="awpcp-form-spacer clearfix">
            <label for="awpcp-billing-card-exp-month"><?php esc_html_e( 'Expiration Date', 'another-wordpress-classifieds-plugin' ); ?></label>
            <div class="awpcp-form-group">
                <label for="awpcp-billing-card-exp-month"><small>mm</small></label>
                <input class="textfield short required" id="awpcp-billing-card-exp-month" type="text" size="2" name="exp_month" value="<?php echo esc_attr( awpcp_array_data( 'exp_month', '', $data ) ); ?>" data-bind="value: exp_month">
                /&nbsp;
            </div>
            <div class="awpcp-form-group">
                <label for="awpcp-billing-card-exp-year"><small>yyyy</small></label>
                <input class="textfield short required" id="awpcp-billing-card-exp-year" type="text" size="2" name="exp_year" value="<?php echo esc_attr( awpcp_array_data( 'exp_year', '', $data ) ); ?>" data-bind="value: exp_year">
            </div>
            <?php awpcp_show_form_error( 'exp_month', $errors ); ?>
            <?php awpcp_show_form_error( 'exp_year', $errors ); ?>
        </div>

        <div class="awpcp-form-spacer clearfix">
            <label for="awpcp-billing-csc"><?php esc_html_e( 'CSC', 'another-wordpress-classifieds-plugin' ); ?></label>
            <input class="textfield required" id="awpcp-billing-csc" type="text" size="50" name="csc" value="<?php echo esc_attr( awpcp_array_data('csc', '', $data ) ); ?>">
            <?php awpcp_show_form_error( 'csc', $errors ); ?>
        </div>
    </fieldset>

    <fieldset>
        <div class="awpcp-form-spacer clearfix">
            <label for="awpcp-billing-first-name"><?php esc_html_e( 'First Name', 'another-wordpress-classifieds-plugin' ); ?></label>
            <input class="textfield required" id="awpcp-billing-first-name" type="text" size="50" name="first_name" value="<?php echo esc_attr( awpcp_array_data('first_name', '', $data ) ); ?>" data-bind="value: first_name">
            <?php awpcp_show_form_error( 'first_name', $errors ); ?>
        </div>

        <div class="awpcp-form-spacer clearfix">
            <label for="awpcp-billing-last-name"><?php esc_html_e( 'Last Name', 'another-wordpress-classifieds-plugin' ); ?></label>
            <input class="textfield required" id="awpcp-billing-last-name" type="text" size="50" name="last_name" value="<?php echo esc_attr( awpcp_array_data('last_name', '', $data ) ); ?>" data-bind="value: last_name">
            <?php awpcp_show_form_error( 'last_name', $errors ); ?>
        </div>

        <div class="awpcp-form-spacer clearfix">
            <label for="awpcp-billing-address-1"><?php esc_html_e( 'Address Line 1', 'another-wordpress-classifieds-plugin' ); ?></label>
            <input class="textfield required" id="awpcp-billing-address-1" type="text" size="50" name="address_1" value="<?php echo esc_attr( awpcp_array_data('address_1', '', $data ) ); ?>" data-bind="value: address_1">
            <?php awpcp_show_form_error( 'address_1', $errors ); ?>
        </div>

        <div class="awpcp-form-spacer clearfix">
            <label for="awpcp-billing-address-2"><?php esc_html_e( 'Address Line 2', 'another-wordpress-classifieds-plugin' ); ?></label>
            <input class="textfield" id="awpcp-billing-address-2" type="text" size="50" name="address_2" value="<?php echo esc_attr( awpcp_array_data('address_2', '', $data ) ); ?>" data-bind="value: address_2">
        </div>

        <div class="awpcp-form-spacer clearfix" data-bind="visible: show_state_field">
            <label for="awpcp-billing-state"><?php esc_html_e( 'State', 'another-wordpress-classifieds-plugin' ); ?></label>
            <input class="textfield required" id="awpcp-billing-state" type="text" size="50" name="state" value="<?php echo esc_attr( awpcp_array_data('state', '', $data ) ); ?>" data-bind="value: state, disable: _country, visible: !_country()">
            <div data-bind="with: _country">
                <select class="required" id="awpcp-billing-state" name="state" data-bind="options: states, optionsText: 'name', optionsValue: 'code', value: $root.state, enable: $root._country, visible: $root._country"></select>
            </div>
            <?php awpcp_show_form_error( 'state', $errors ); ?>
        </div>

        <div class="awpcp-form-spacer clearfix">
            <label for="awpcp-billing-city"><?php esc_html_e( 'City', 'another-wordpress-classifieds-plugin' ); ?></label>
            <input class="textfield required" id="awpcp-billing-city" type="text" size="50" name="city" value="<?php echo esc_attr( awpcp_array_data('city', '', $data ) ); ?>" data-bind="value: city">
            <?php awpcp_show_form_error( 'city', $errors ); ?>
        </div>

        <div class="awpcp-form-spacer clearfix">
            <label for="awpcp-billing-postal-code"><?php esc_html_e( 'Postal Code', 'another-wordpress-classifieds-plugin' ); ?></label>
            <input class="textfield required" id="awpcp-billing-postal-code" type="text" size="50" name="postal_code" value="<?php echo esc_attr( awpcp_array_data('postal_code', '', $data ) ); ?>" data-bind="value: postal_code">
            <?php awpcp_show_form_error( 'postal_code', $errors ); ?>
        </div>

        <div class="awpcp-form-spacer clearfix">
            <label for="awpcp-billing-email"><?php esc_html_e( 'Email', 'another-wordpress-classifieds-plugin' ); ?></label>
            <input class="textfield required" id="awpcp-billing-email" type="text" size="50" name="email" value="<?php echo esc_attr( awpcp_array_data('email', '', $data ) ); ?>" data-bind="value: email">
            <?php awpcp_show_form_error( 'email', $errors ); ?>
        </div>
    </fieldset>

    <p class="awpcp-form-submit">
        <?php foreach ($hidden as $name => $value): ?>
        <input type="hidden" value="<?php echo esc_attr($value) ?>" name="<?php echo esc_attr($name) ?>">
        <?php endforeach ?>
        <input class="button" type="submit" value="<?php esc_attr_e( 'Cancel', 'another-wordpress-classifieds-plugin' ); ?>" id="submit" name="cancel">
        <input class="button" type="submit" value="<?php esc_attr_e( 'Continue', 'another-wordpress-classifieds-plugin' ); ?>" id="submit" name="submit">
    </p>
</form>
