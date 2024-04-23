<div id="classiwrapper">

	<h2><?php esc_html_e( 'Renew Ad', 'another-wordpress-classifieds-plugin' ); ?></h2>

<?php if (in_array($step, array('renew-ad', 'error', 'post-checkout'))): ?>

	<?php echo $content ?>

<?php elseif ($step == 'checkout'): ?>

	<?php foreach ($header as $part): ?>
	<p><?php echo $part ?></p>
	<?php endforeach ?>

	<?php $msg = __( 'Please click the payment button below to proceed with Payment for your Ad renewal. You will be asked to pay %s.', 'another-wordpress-classifieds-plugin') ?>
	<p><?php printf( esc_html( $msg ), $amount ); ?></p>
	<?php echo $content ?>

<?php endif ?>

</div>
