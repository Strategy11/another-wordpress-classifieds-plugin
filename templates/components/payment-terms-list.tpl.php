<ul class="awpcp-payment-terms-list">
    <?php foreach ( $payment_terms as $payment_term ): ?>
    <li class="awpcp-payment-terms-list-payment-term awpcp-rounded-box awpcp-bordered-box awpcp-box awpcp-clearfix">
        <div class="awpcp-payment-term-duration">
            <span class="awpcp-payment-term-duration-amount"><?php echo esc_html( $payment_term['duration_amount'] ); ?></span>
            <span class="awpcp-payment-term-duration-interval"><?php echo esc_html( $payment_term['duration_interval'] ); ?></span>
        </div>
        <div class="awpcp-payment-term-content">
            <span class="awpcp-payment-term-name"><?php echo esc_html( $payment_term['name'] ); ?></span>
            <?php if ( ! empty( $payment_term['description'] ) ): ?>
            <div class="awpcp-payment-term-description"><?php echo esc_html( $payment_term['description'] ); ?></div>
            <?php endif; ?>
            <ul class="awpcp-payment-term-features">
                <?php foreach ( $payment_term['features'] as $feature ): ?>
                <li class="awpcp-payment-term-feature"><?php echo $feature; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="awpcp-payment-term-price">
            <div class="awpcp-payment-term-price-in-currency">
                <!--<span><?php echo esc_html( $payment_term['price']['currency_amount'] ); ?></span>
                <a href="#" class="button"><?php echo esc_html( $payment_term['price']['currency_button_label'] ); ?></a>-->
                <label><input type="radio" name="payment_term" value="<?php echo esc_html( $payment_term['price']['currency_option'] ); ?>">&nbsp;<?php echo esc_html( $payment_term['price']['currency_amount'] ); ?></label>
            </div>
            <div class="awpcp-payment-term-price-in-credits">
                <!--<span><?php echo esc_html( $payment_term['price']['credits_amount'] ); ?></span>
                <span><?php echo esc_html( $payment_term['price']['credits_label'] ); ?></span>
                <a href="#" class="button"><?php echo esc_html( $payment_term['price']['credits_button_label'] ); ?></a>-->
                <label><input type="radio" name="payment_term" value="<?php echo esc_html( $payment_term['price']['credits_option'] ); ?>">&nbsp;<?php echo esc_html( $payment_term['price']['credits_amount'] ); ?>&nbsp;<?php echo esc_html( $payment_term['price']['credits_label'] ); ?></label>
            </div>
        </div>
        <!-- extra -->
    </li>
    <?php endforeach; ?>
</ul>
