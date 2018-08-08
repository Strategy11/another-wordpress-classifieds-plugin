<div class="pager">
    <div class="awpcp-pagination-links"><?php echo implode( '', $items ); // XSS Ok. ?></div>
    <form class="awpcp-pagination-form" method="get">
        <?php echo awpcp_html_hidden_fields( $params ); ?>

        <?php if ( count( $options ) > 1 ): ?>

        <select name="results">
        <?php foreach ($options as $option): ?>
            <?php if ($results == $option): ?>
            <option value="<?php echo esc_attr( $option ); ?>" selected="selected"><?php echo esc_html( $option ); ?></option>
            <?php else: ?>
            <option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
        </select>

        <?php endif; ?>
    </form>
</div>
