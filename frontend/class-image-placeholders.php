<?php

function awpcp_image_placeholders() {
    return new AWPCP_Image_Placeholders(
        awpcp_media_api(),
        awpcp_attachment_properties(),
        awpcp_attachments_collection()
    );
}

class AWPCP_Image_Placeholders {

    private $media_api;
    private $attachment_properties;
    private $attachments;

    private $cache;

    public function __construct( $media_api, $attachment_properties, $attachments ) {
        $this->media_api = $media_api;
        $this->attachment_properties = $attachment_properties;
        $this->attachments = $attachments;
    }

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

        $url = awpcp_listing_renderer()->get_view_listing_url( $ad );
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

                $image_dimensions = $this->media_api->get_metadata( $primary_image, 'image-dimensions', array() );
                $image_dimensions = awpcp_array_data( 'primary', array(), $image_dimensions );

                $link_attributes = array(
                    'class' => 'awpcp-listing-primary-image-thickbox-link thickbox thumbnail',
                    'href' => esc_url( $large_image ),
                    'rel' => esc_attr( $gallery_name ),
                );

                // single ad
                $image_attributes = array(
                    'attributes' => array(
                        'class' => 'thumbshow',
                        'src' => esc_attr( $thumbnail ),
                        'width' => awpcp_array_data( 'width', null, $image_dimensions ),
                        'height' => awpcp_array_data( 'height', null, $image_dimensions ),
                    )
                );

                $content = '<div class="awpcp-ad-primary-image">';
                $content.= '<a ' . awpcp_html_attributes( $link_attributes ) . '>';
                $content.= awpcp_html_image( $image_attributes );
                $content.= '</a>' . $link;
                $content.= '</div>';

                $placeholders['featureimg'] = $content;

                // listings
                $image_attributes = array(
                    'attributes' => array(
                        'alt' => awpcp_esc_attr( awpcp_listing_renderer()->get_listing_title( $ad ) ),
                        'src' => esc_attr( $thumbnail ),
                        'width' => $thumbnail_width,
                    )
                );

                $content = '<a class="awpcp-listing-primary-image-listing-link" href="%s">%s</a>';
                $content = sprintf( $content, $url, awpcp_html_image( $image_attributes ) );

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
                    $thumbnail = $this->attachment_properties->get_image_url( $image, 'thumbnail' );

                    $image_dimensions = awpcp_media_api()->get_metadata( $image, 'image-dimensions', array() );
                    $image_dimensions = awpcp_array_data( 'thumbnail', array(), $image_dimensions );

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

                    $image_attributes = array(
                        'attributes' => array(
                            'class' => 'thumbshow',
                            'src' => esc_attr( $thumbnail ),
                            'width' => awpcp_array_data( 'width', null, $image_dimensions ),
                            'height' => awpcp_array_data( 'height', null, $image_dimensions ),
                        )
                    );

                    $content = '<li ' . awpcp_html_attributes( $li_attributes ) . '>';
                    $content.= '<a ' . awpcp_html_attributes( $link_attributes ) . '>';
                    $content.= awpcp_html_image( $image_attributes );
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
            $thumbnail = sprintf('%s/adhasnoimage.png', $awpcp_imagesurl);

            $image_attributes = array(
                'attributes' => array(
                    'alt' => awpcp_esc_attr( $ad->ad_title ),
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
