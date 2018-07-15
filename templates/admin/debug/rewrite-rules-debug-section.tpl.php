<table class="widefat striped">
    <thead>
        <tr>
            <th><?php _e('Pattern', 'another-wordpress-classifieds-plugin') ?></th>
            <th><?php _e('Replacement', 'another-wordpress-classifieds-plugin') ?></th>
        </tr>
    </thead>
    <tbody>
<?php foreach($rules as $pattern => $rule): ?>
        <tr>
            <td><?php echo $pattern ?></td>
            <td><?php echo $rule ?></td>
        </tr>
<?php endforeach ?>
    </tbody>
</table>

