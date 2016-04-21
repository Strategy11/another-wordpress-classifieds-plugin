<?php

function awpcp_image_placeholders() {
    return new AWPCP_Image_Placeholders(
        awpcp_attachment_properties(),
        awpcp_attachments_collection(),
        awpcp_listing_renderer()
    );
}

class AWPCP_Image_Placeholders {

    private $attachment_properties;
    private $attachments;
    private $listing_renderer;

    private $cache;

    public function __construct( $attachment_properties, $attachments, $listing_renderer ) {
        $this->attachment_properties = $attachment_properties;
        $this->attachments = $attachments;
        $this->listing_renderer = $listing_renderer;
    }

    /**
     * TODO: test image src, width and other properties are properly rendered.
     */
    public function do_image_placeholders( $ad, $placeholder ) {
        if ( ! isset( $this->cache[ $ad->ID ] ) ) {
            $placeholders = $this->render_image_placeholders( $ad, $placeholder );
            $this->cache[ $ad->ID ] = apply_filters( 'awpcp-image-placeholders', $placeholders, $ad );
        }

        return $this->cache[ $ad->ID ][ $placeholder ];
    }

    private function render_image_placeholders( $ad, $placeholder ) {
        global $awpcp_imagesurl;

        $placeholders = array(
            'featureimg' => '',
            'awpcpshowadotherimages' => '',
            'images' => '',
            'awpcp_image_name_srccode' => '',
        );

        $url = $this->listing_renderer->get_view_listing_url( $ad );
        $thumbnail_width = get_awpcp_option('displayadthumbwidth');

        if ( awpcp_are_images_allowed() ) {
            $primary_image = $this->attachments->get_featured_attachment_of_type(
                'image',
                array( 'post_parent' => $ad->ID )
            );

            $gallery_name = 'awpcp-gallery-' . $ad->ID;

            if ($primary_image) {
                $large_image = $this->attachment_properties->get_image_url( $primary_image, 'large' );
                $thumbnail = $this->attachment_properties->get_image_url( $primary_image, 'featured' );

                if (get_awpcp_option('show-click-to-enlarge-link', 1)) {
                    $link = '<a class="thickbox enlarge" href="%s">%s</a>';
                    $link = sprintf($link, $large_image, __('Click to enlarge image.', 'another-wordpress-classifieds-plugin'));
                } else {
                    $link = '';
                }

                $link_attributes = array(
                    'class' => 'awpcp-listing-primary-image-thickbox-link thickbox thumbnail',
                    'href' => esc_url( $large_image ),
                    'rel' => esc_attr( $gallery_name ),
                );

                $content = '<div class="awpcp-ad-primary-image">';
                $content.= '<a ' . awpcp_html_attributes( $link_attributes ) . '>';
                $content.= wp_get_attachment_image( $primary_image->ID, 'awpcp-featured', false, array( 'class' => 'thumbshow' ) );
                $content.= '</a>' . $link;
                $content.= '</div>';

                $placeholders['featureimg'] = $content;

                // listings
                $image_attributes = array( 'alt' => awpcp_esc_attr( $this->listing_renderer->get_listing_title( $ad ) ) );

                $content = '<a class="awpcp-listing-primary-image-listing-link" href="%s">%s</a>';
                // TODO: Can we define a custom image size everytime the user changes the displayadthumbwidth setting, and regenerate
                //       thumbnails for that.
                $content = sprintf( $content, $url, wp_get_attachment_image( $primary_image->ID, array( $thumbnail_width, 0 ), false, $image_attributes ) );

                $placeholders['awpcp_image_name_srccode'] = $content;
            }

            $images_uploaded = $this->attachments->count_attachments_of_type( 'image', array( 'post_parent' => $ad->ID ) );

            if ($images_uploaded >= 1) {
                $results = $this->attachments->find_visible_attachments( array( 'post_parent' => $ad->ID ) );

                $columns = get_awpcp_option('display-thumbnails-in-columns', 0);
                $rows = $columns > 0 ? ceil(count($results) / $columns) : 0;
                $shown = 0;

                $images = array();
                foreach ($results as $image) {
                    if ( $primary_image && $primary_image->ID == $image->ID ) {
                        continue;
                    }

                    $large_image = $this->attachment_properties->get_image_url( $image, 'large' );

                    if ($columns > 0) {
                        $li_attributes['class'] = join(' ', awpcp_get_grid_item_css_class(array(), $shown, $columns, $rows));
                    } else {
                        $li_attributes['class'] = '';
                    }

                    $link_attributes = array(
                        'class' => 'thickbox',
                        'href' => esc_url( $large_image ),
                        'rel' => esc_attr( $gallery_name )
                    );

                    $content = '<li ' . awpcp_html_attributes( $li_attributes ) . '>';
                    $content.= '<a ' . awpcp_html_attributes( $link_attributes ) . '>';
                    $content.= wp_get_attachment_image( $image->ID, 'awpcp-thumbnail', false, array( 'class' => 'thumbshow' ) );
                    $content.= '</a>';
                    $content.= '</li>';

                    $images[] = $content;

                    $shown = $shown + 1;
                }

                $placeholders['awpcpshowadotherimages'] = join('', $images);

                $content = '<ul class="awpcp-single-ad-images">%s</ul>';
                $placeholders['images'] = sprintf($content, $placeholders['awpcpshowadotherimages']);
            }
        }

        // fallback thumbnail
        if ( awpcp_are_images_allowed() && empty( $placeholders['awpcp_image_name_srccode'] ) ) {

			// check if user has enabled override for no image placeholder
			if ( get_awpcp_option( 'override-noimage-placeholder', true ) ) {
				// get saved no image placeholer url
				$thumbnail = get_awpcp_option( 'noimage-placeholder-url' );

			}else {
				$thumbnail = sprintf( '%s/adhasnoimage.png', $awpcp_imagesurl );
			}

            $image_attributes = array(
                'attributes' => array(
                    'alt' => awpcp_esc_attr( $this->listing_renderer->get_listing_title( $ad ) ),
                    'src' => esc_attr( $thumbnail ),
                    'width' => esc_attr( $thumbnail_width ),
                )
            );

            $content = '<a class="awpcp-listing-primary-image-listing-link" href="%s">%s</a>';
            $content = sprintf($content, $url, awpcp_html_image( $image_attributes ) );

            $placeholders['awpcp_image_name_srccode'] = $content;
        }

        $placeholders['featureimg'] = apply_filters( 'awpcp-featured-image-placeholder', $placeholders['featureimg'], 'single', $ad );
        $placeholders['awpcp_image_name_srccode'] = apply_filters( 'awpcp-featured-image-placeholder', $placeholders['awpcp_image_name_srccode'], 'listings', $ad );

        $placeholders['featured_image'] = $placeholders['featureimg'];
        $placeholders['imgblockwidth'] = "{$thumbnail_width}px";
        $placeholders['thumbnail_width'] = "{$thumbnail_width}px";

        return $placeholders;
    }
}
