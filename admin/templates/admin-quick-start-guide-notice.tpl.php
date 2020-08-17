<div id="quick-start-guide-notice" class="update-nag awpcp-update-nag awpcp-sticky-notice clearfix">
    <p class="align-center"><?php _e('Hello and welcome to <strong>Another WordPress Classifieds</strong>. This plugin is super easy to use AND highly configurable.', 'another-wordpress-classifieds-plugin') ?></p>
    <p class="align-center"><?php _e('Would you like some help getting started?', 'another-wordpress-classifieds-plugin') ?></p>

    <div class="actions align-center">
        <div style="float:left;width:50%">
            <?php $text = _x('No Thanks', 'Quick Start Guide', 'another-wordpress-classifieds-plugin') ?>
            <p class="align-center"><a id="link-no-thanks" class="button" title="<?php echo esc_attr($text) ?>" data-action="disable-quick-start-guide-notice"><?php echo $text ?></a><br/>
                <?php _ex("I'll figure it out on my own.", 'Quick Start Guide', 'another-wordpress-classifieds-plugin') ?></p>
        </div>
        <div style="float:left;width:50%">
            <?php $text = _x('Yes Please!', 'Quick Start Guide', 'another-wordpress-classifieds-plugin') ?>
            <?php $url = esc_attr('https://awpcp.com/quick-start-guide') ?>
            <p class="align-center"><a id="link-no-thanks" class="button button-primary" href="<?php echo $url ?>" title="<?php echo esc_attr($text) ?>" target="_blank" data-action="disable-quick-start-guide-notice"><?php echo $text ?></a><br/>
                <?php _ex("Help me get my classifieds running quickly.", 'Quick Start Guide', 'another-wordpress-classifieds-plugin') ?></p>
        </div>
    </div>
</div>
