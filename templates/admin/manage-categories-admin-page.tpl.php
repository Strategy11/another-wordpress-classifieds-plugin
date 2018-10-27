<?php
/**
 * @package AWPCP\Templates\Admin
 */

// phpcs:disable

?><div class="awpcp-manage-categories-category-form-container postbox-container">
    <div class="metabox-holder">
        <div class="metabox-sortables">
            <div class="postbox">
                <?php
                    echo awpcp_html_admin_third_level_heading(array(
                        'content' => $form_title,
                        'attributes' => array( 'class' => 'hndle' ),
                    ));
                ?>
                <div class="inside">
                    <form id="awpcp_launch" class="awpcp-manage-categories-category-form" method="post">
                        <input type="hidden" name="awpcp-action" value="<?php echo $form_values['action']; ?>" />
                        <input type="hidden" name="category_id" value="<?php echo $form_values['category_id']; ?>" />
                        <input type="hidden" name="aeaction" value="<?php echo $form_values['action']; ?>" />
                        <input type="hidden" name="offset" value="<?php echo $offset; ?>" />
                        <input type="hidden" name="results" value="<?php echo $results; ?>" />

                        <div class="awpcp-clearfix clearfix">
                        <div class="awpcp-manage-categories-category-form-field awpcp-manage-categories-category-form-name-field">
                            <label for="cat_name"><?php echo __( 'Category Name', 'another-wordpress-classifieds-plugin' ); ?></label>
                            <input id="cat_name" type="text" name="category_name" value="<?php echo $form_values['category_name']; ?>"/>
                        </div>
                        <div class="awpcp-manage-categories-category-form-field">
                            <label for="awpcp-category-parent-field"><?php echo __( 'Category Parent', 'another-wordpress-classifieds-plugin' ); ?></label>
                            <select id="awpcp-category-parent-field" name="category_parent_id">
                                <option value="0"><?php echo __( 'No parent, this a top level category','another-wordpress-classifieds-plugin' ); ?></option>
                                <?php echo get_categorynameid( $form_values['category_id'], $form_values['category_parent_id'] ); ?>
                            </select>
                        </div>
                        <div class="awpcp-manage-categories-category-form-field">
                            <label for="category_order"><?php echo __( 'Category list order', 'another-wordpress-classifieds-plugin' ); ?></label>
                            <input id="category_order" type="text" name="category_order" value="<?php echo $form_values['category_order']; ?>"/>
                        </div>
                        </div>

                        <?php // TODO: allow other sections to enter content before the submit button ?>
                        <?php // echo $promptmovetocat; ?>

                        <p class="submit inline-edit-save">
                            <input type="submit" class="button-primary button" name="createeditadcategory" value="<?php echo $form_submit; ?>" />
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="awpcp-manage-categories-icons-meaning">
    <ul>
        <li class="awpcp-manage-categories-icons-meaning-header">
            <span><?php echo __( 'Icon Meanings:', 'another-wordpress-classifieds-plugin' ); ?></span>
        </li>
    <?php foreach ( $icons as $icon ): ?>
        <li class="awpcp-manage-categories-icons-meaning-icon"><i class="<?php echo esc_attr( $icon['class'] ); ?>"></i><span><?php echo $icon['label']; ?></span></li>
    <?php endforeach; ?>
    </ul>
</div>

<form id="mycats" class="awpcp-clearboth" name="mycats" method="post">
    <p>
        <input type="submit" name="awpcp-delete-multiple-categories" class="button" value="<?php echo __( "Delete Selected Categories",'another-wordpress-classifieds-plugin' ); ?>"/>
        <input type="submit" name="awpcp-move-multiple-categories" class="button" value="<?php echo __( "Move Selected Categories",'another-wordpress-classifieds-plugin' ); ?>"/>
        <select name="moveadstocategory">
            <option value="0"><?php echo __( "Select Move-To category",'another-wordpress-classifieds-plugin' ); ?></option>
            <?php echo get_categorynameid( $cat_id = 0, $cat_parent_id = 0, $exclude='' ); ?>
        </select>
    </p>

    <?php echo $pager1; ?>

    <p>
        <?php echo __( 'Delete categories should do this with existing Ads', 'another-wordpress-classifieds-plugin' ); ?>
        <label><input type="radio" name="movedeleteads" value="1" checked='checked' ><?php echo __( 'Move Ads to new category', 'another-wordpress-classifieds-plugin' ); ?></label>
        <label><input type="radio" name="movedeleteads" value="2" ><?php echo __( 'Delete Ads too', 'another-wordpress-classifieds-plugin' ); ?></label>
    </p>

    <style>
        table.listcatsh { width: 100%; padding: 0px; border: none; border: 1px solid #dddddd;}
        table.listcatsh td { font-size: 12px; border: none; background-color: #F4F4F4;
        vertical-align: middle; font-weight: bold; }
        table.listcatsh tr.special td { border-bottom: 1px solid #ff0000;  }
        table.listcatsc { width: 100%; padding: 0px; border: none; border: 1px solid #dddddd;}
        table.listcatsc td { width:33%;border: none;
        vertical-align: middle; padding: 5px; font-weight: normal; }
        table.listcatsc tr.special td { border-bottom: 1px solid #ff0000;  }
    </style>

    <table class="listcatsh">
        <tr>
            <td style="width:4%;padding:5px;text-align:center">
                <label class="screen-reader-text" for="awpcp-category-select-all"><?php esc_html_e( 'Select all categories', 'another-wordpress-classifieds-plugin' ); ?></label>
                <input id="awpcp-category-select-all" type="checkbox" onclick="CheckAll()" />
            </td>
            <td style="width:15%; text-align: center;"><?php esc_html_e( 'Category ID', 'another-wordpress-classifieds-plugin' ); ?></td>
            <td style="width:33%;padding:5px;">
                <?php esc_html_e( 'Category Name (Total Ads)', 'another-wordpress-classifieds-plugin' ); ?>
            </td>
            <td style="width:28%;padding:5px;"><?php echo __( 'Parent', 'another-wordpress-classifieds-plugin' ); ?></td>
            <td style="width:5%;padding:5px;"><?php echo __( 'Order', 'another-wordpress-classifieds-plugin' ); ?></td>
            <td style="width:15%;padding:5px;;"><?php echo __( 'Action', 'another-wordpress-classifieds-plugin' ); ?></td>
        </tr>

        <?php echo smart_table2( $items, 1, '', '', false ); ?>

        <tr>
            <td style="padding:5px"></td>
            <td style="width:10%; text-align: center;"><?php echo __( 'Category ID', 'another-wordpress-classifieds-plugin' ); ?></td>
            <td style="padding:5px;"><?php echo __( 'Category Name (Total Ads)', 'another-wordpress-classifieds-plugin' ); ?></td>
            <td style="padding:5px;"><?php echo __( 'Parent', 'another-wordpress-classifieds-plugin' ); ?></td>
            <td style="padding:5px;"><?php echo __("Order",'another-wordpress-classifieds-plugin'); ?></td>
            <td style="padding:5px;"><?php echo __("Action",'another-wordpress-classifieds-plugin'); ?></td>
        </tr>
    </table>
</form>
<?php echo $pager2; ?>
