<?php
/**
 * @package AWPCP\Templates
 */

?><div class="awpcp-listing-fields-metabox awpcp-metabox-tabs">
    <ul class="awpcp-tabs">
        <?php $label = _x( 'Form Fields', 'listing fields metabox', 'another-wordpress-classifieds-plugin' ); ?>
        <li class="awpcp-tab awpcp-tab-active"><a href="#awpcp-listing-fields--form-fields"><span class="screen-reader-text"><?php echo esc_html( $label ); ?></span><span class="dashicons dashicons-feedback"></span><span class="awpcp-tab-name"><?php echo esc_html( $label ); ?></span></a></li>
        <?php $label = _x( 'Start/End Date', 'listing fields metabox', 'another-wordpress-classifieds-plugin' ); ?>
        <li class="awpcp-tab"><a href="#awpcp-listing-fields--start-end-date"><span class="screen-reader-text"><?php echo esc_html( $label ); ?></span><span class="dashicons dashicons-calendar-alt"></span><span class="awpcp-tab-name"><?php echo esc_html( $label ); ?></span></a></li>
        <?php $label = _x( 'Images', 'listing fields metabox', 'another-wordpress-classifieds-plugin' ); ?>
        <li class="awpcp-tab"><a href="#awpcp-listing-fields--media-manager"><span class="screen-reader-text"><?php echo esc_html( $label ); ?></span><span class="dashicons dashicons-format-gallery"></span><span class="awpcp-tab-name"><?php echo esc_html( $label ); ?></span></a></li>
    </ul>
    <div id="awpcp-listing-fields--form-fields" class="awpcp-tab-panel awpcp-tab-panel-active"><?php echo $details_form_fields; // XSS Ok. ?></div>
    <div id="awpcp-listing-fields--start-end-date" class="awpcp-tab-panel"><?php echo $date_form_fields; // XSS Ok. ?></div>
    <div id="awpcp-listing-fields--media-manager" class="awpcp-tab-panel"><?php echo $media_manager; // XSS Ok. ?></div>
</div>
