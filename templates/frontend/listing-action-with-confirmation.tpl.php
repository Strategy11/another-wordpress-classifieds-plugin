<?php /* ?>
<?php $css_class = esc_attr( "awpcp-listing-action-" . $this->get_slug() ); ?>
<form class="awpcp-listing-action-form awpcp-listing-action-form-with-confirmation <?php echo $css_class; ?>-form <?php echo $css_class; ?>-form-with-confirmation" action="<?php echo esc_attr( $this->get_endpoint( $listing, $config ) ); ?>" method="post">
    <?php echo awpcp_html_hidden_fields( $this->filter_params( $config['hidden-params'] ) ); ?>

    <!-- <span class="<?php echo $css_class; ?>-description"><?php echo esc_html( $this->get_description() ); ?></span>-->
    <span class="awpcp-listing-action-form-confirmation is-hidden"><?php echo esc_html( $this->get_confirmation_message() ); ?></span>

    <input class="awpcp-listing-action-form-cancel-button button is-hidden" type="button" value="<?php echo esc_attr( $this->get_cancel_button_label() ); ?>" />
    <input class="awpcp-listing-action-form-submit-button button-primary" type="submit" value="<?php echo esc_attr( $this->get_submit_button_label() ); ?>" />
</form>
<?php */ ?>
<button data-action="<?php echo esc_attr( $this->get_slug() ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-confirmation-message="<?php echo esc_attr( $this->get_confirmation_message() ); ?>" data-cancel-button="<?php echo esc_attr( $this->get_cancel_button_label() ); ?>"><?php echo esc_html( $this->get_submit_button_label() ); ?></button>
