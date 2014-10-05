<div class="apwcp-media-manager">
    <div class="awpcp-uploaded-images" data-bind="if: haveImages">
        <h3><?php echo esc_html( __( 'Images', 'AWPCP' ) ); ?></h3>
        <ul class="awpcp-uploaded-files-list clearfix" data-bind="foreach: { data: images, as: 'image' }">
            <li data-bind="css: $root.getFileCSSClasses( image ), attr: { id: $root.getFileId( image ) }">
                <div class="awpcp-uploaded-file-thumbnail-container">
                    <img data-bind="attr: { src: thumbnailUrl }">
                </div>
                <ul class="awpcp-uploaded-file-actions clearfix">
                    <li class="awpcp-uploaded-file-action awpcp-uploaded-file-change-status-action">
                        <label>
                            <input type="checkbox" data-bind="checked: enabled"> <?php echo esc_html( __( 'Enabled', 'AWPCP' ) ); ?>
                        </label>
                    </li>
                    <li class="awpcp-uploaded-file-action awpcp-uploaded-file-set-as-primary-action">
                        <span>
                            <a href="#" title="<?php echo esc_attr( __( 'This is the Primary Image', 'AWPCP' ) ); ?>" data-bind="visible: isPrimary(), click: function() {}"></a>
                            <a href="#" title="<?php echo esc_attr( __( 'Set as Primary Image', 'AWPCP' ) ); ?>" data-bind="visible: !isPrimary(), click: $root.setFileAsPrimary"></a>
                        </span>
                    </li>
                    <li class="awpcp-uploaded-file-action awpcp-uploaded-file-delete-action"><a title="<?php echo esc_attr( __( 'Delete Image', 'AWPCP' ) ); ?>" data-bind="click: $root.deleteFile"></a></li>
                    <li class="awpcp-uploaded-file-action awpcp-uploaded-file-loading-icon" data-bind="visible: isBeingModified"><span class="awpcp-spinner awpcp-spinner-visible"></span></li>
                </ul>
                <div class="awpcp-uploaded-file-primary-label" data-bind="visible: isPrimary"><?php echo esc_html(  __( 'Primary Image', 'AWPCP' ) ); ?></div>
            </li>
        </ul>
    </div>

    <div class="awpcp-uploaded-images" data-bind="if: haveVideos">
        <h3><?php echo esc_html( __( 'Videos', 'AWPCP' ) ); ?></h3>
        <ul>
            <li>
                <img>
                <ul>
                    <li><a href="#"></a></li>
                </ul>
            </li>
        </ul>
    </div>


    <div class="awpcp-uploaded-images" data-bind="if: haveOtherFiles">
        <h3><?php echo esc_html( __( 'Other Files', 'AWPCP' ) ); ?></h3>
        <table>
            <tbody>
                <tr>
                    <td></td>
                    <td>
                        <ul>
                            <li><a href="#"></a></li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>




