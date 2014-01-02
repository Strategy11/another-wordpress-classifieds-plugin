<nav class="awpcp-navigation awpcp-menu-items-container clearfix">
    <h1 class="awpcp-menu-toggle"><?php _e( 'Classifieds Menu', 'AWPCP' ); ?></h1>
    <div class="awpcp-nav-menu">
        <ul class="awpcp-menu-items">
        <?php foreach ( $menu_items as $item => $parts ): ?>
            <li class="<?php echo esc_attr( $item ); ?>"><a href="<?php echo esc_attr( $parts['url'] ); ?>"><?php echo $parts['title']; ?></a></li>
        <?php endforeach; ?>
        </ul>
    </div>
</nav>
