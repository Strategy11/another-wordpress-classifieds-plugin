<?php if ( count( $menu_items ) > 0 ): ?>
<div class="awpcp-navigation awpcp-menu-items-container clearfix">
    <span class="awpcp-menu-toggle"><?php echo esc_html( __( 'Classifieds Menu', 'AWPCP' ) ); ?></span>
    <div class="awpcp-nav-menu">
        <ul class="awpcp-menu-items clearfix">
        <?php foreach ( $menu_items as $item => $parts ): ?>
            <li class="<?php echo esc_attr( $item ); ?>"><a href="<?php echo esc_attr( $parts['url'] ); ?>"><?php echo esc_html( $parts['title'] ); ?></a></li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>
