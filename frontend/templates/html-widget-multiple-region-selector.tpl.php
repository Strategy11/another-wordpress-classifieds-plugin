<div id="awpcp-multiple-region-selector-<?php echo $uuid; ?>" class="awpcp-multiple-region-selector" uuid="<?php echo $uuid; ?>">
    <!-- ko foreach: regions -->
    <div class="awpcp-region-selector">
        <!-- ko foreach: partials -->
        <div class="awpcp-region-selector-partial" data-bind="visible: visible">
            <label data-bind="attr: { 'for': id }, text: label"></label>

            <select class="multiple-region" data-bind="attr: { id: id, name: selectName }, options: options, optionsText: 'name', optionsValue: 'id', optionsCaption: caption, value: selectedOption, visible: showSelectField, disable: $root.options.disabled">
            </select>

            <input class="multiple-region" type="text" data-bind="attr: { id: id, name: textfieldName }, value: selectedText, visible: showTextField, disable: $root.options.disabled" />

            <span class="loading-message" data-bind="visible: loading"><?php echo _x( 'loading...', 'loading region options', 'AWPCP' ); ?></span>

            <input type="hidden" data-bind="attr: { name: param }, value: selected" />
        </div>
        <!-- /ko -->
        <a class="button remove-region" href="#" data-bind="click: $root.onRemoveRegion(), visible: $root.showRemoveRegionButton"><?php echo __( 'Remove Region', 'AWPCP' ); ?></a>
        <span class="awpcp-error" data-bind="text: error, visible: error"></span>
    </div>
    <!-- /ko -->
    <a class="button add-region" href="#" data-bind="click: onAddRegion, visible: showAddRegionButton"><?php echo __( 'Add Region', 'AWPCP' ); ?></a>
    <?php echo awpcp_form_error('regions', $errors); ?>
</div>
