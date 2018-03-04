<?php
/**
 * @package AWPCP/Listings
 */

/**
 * Clases used to define permalink structure and create listings permalinks.
 */
class AWPCP_ListingsPermalinks {
    /**
     * @var string  Post type identifier.
     */
    private $post_type;

    /**
     * @var string  Name of the Category taxonomy.
     */
    private $category_taxonomy;

    /**
     * Constructor.
     *
     * @since 4.0
     */
    public function __construct( $post_type, $category_taxonomy, $listing_renderer, $settings ) {
        $this->post_type = $post_type;
        $this->category_taxonomy = $category_taxonomy;
        $this->listing_renderer = $listing_renderer;
        $this->settings = $settings;
    }

    /**
     * TODO: move to a Custom Post Types Rewrite Rules class.
     *
     * @since 4.0
     */
    public function update_post_type_permastruct( $post_type, $post_type_object ) {
        if ( $this->post_type != $post_type ) {
            return;
        }

        $permastruct = $this->get_post_type_permastruct( $post_type_object );
        $permastruct_args = array(
            'with_front' => $post_type_object->rewrite['with_front'],
            // If the permalinks are disabled ep_mask, pages and feeds keys are not defined.
            'ep_mask' => isset( $post_type_object->rewrite['ep_mask'] ) ? $post_type_object->rewrite['ep_mask'] : EP_PAGES,
            'paged' => ! empty( $post_type_object->rewrite['pages'] ),
            'feed' => ! empty( $post_type_object->rewrite['feeds'] ),
        );

        if ( is_null( $permastruct ) ) {
            return;
        }

        add_rewrite_tag( '%awpcp_listing_id%', '([0-9]+)', "post_type={$this->post_type}&p=" );
        add_rewrite_tag( '%awpcp_category%', '([^/]+)', "{$this->category_taxonomy}=" );
        add_rewrite_tag( '%awpcp_location%', '(.+?)', '_=' );

        if ( $this->settings->get_option( 'display-listings-as-single-posts' ) ) {
            add_rewrite_tag( '%awpcp_optional_listing_id%', '?(.*)', "_=" );
        } else {
            $show_listing_page_id = $this->get_show_listing_page_id();

            add_rewrite_tag( '%awpcp_optional_listing_id%', '?(.*)', "page_id={$show_listing_page_id}&_=" );

            $permastruct_args['paged'] = false;
            $permastruct_args['feed'] = false;
        }

        add_permastruct( $this->post_type, $permastruct, $permastruct_args );
    }

    /**
     * TODO: Take this as a constructor argument. Define it on the container and pass it
     *       to all classes that need it.
     *
     *       Watch out for race conditions between container initialziation and defininf or storing settings.
     *
     * @since 4.0.0
     */
    private function get_show_listing_page_id() {
        $show_listing_page_id = awpcp_get_page_id_by_ref( 'show-ads-page-name' );
        return $show_listing_page_id ? $show_listing_page_id : -1;
    }

    /**
     * TODO: Allow admins to configure this setting as they configure the global
     *       permalink structure.
     *
     * Default structure: "/{$classifieds_slug}/{$post_type_slug}/%awpcp_listing_id%/%{$post_type_name}%/%awpcp_location%/%awpcp_category%/";
     *
     * @since 4.0
     */
    public function get_post_type_permastruct( $post_type_object ) {
        $permalink_structure = get_option( 'permalink_structure' );

        if ( ! $permalink_structure ) {
            return null;
        }

        $post_type_slug = $post_type_object->rewrite['slug'];

        if ( ! $this->settings->get_option( 'seofriendlyurls' ) ) {
            return "{$post_type_slug}/%awpcp_optional_listing_id%";
        }

        $parts = array( $post_type_slug, '%awpcp_listing_id%' );

        if ( get_awpcp_option( 'include-title-in-listing-url' ) ) {
            $parts[] = "%{$this->post_type}%";
        }

        // TODO: Check if any of the location parts is enabled.
        if( get_awpcp_option( 'include-city-in-listing-url' ) ) {
            $parts[] = '%awpcp_location%';
        }

        if( get_awpcp_option( 'include-category-in-listing-url' ) ) {
            $parts[] = '%awpcp_category%';
        }

        return implode( '/', $parts );
    }

    /**
     * Necessary to support non SEO friendly URLs when permalinks are enabled:
     *
     * http://next.awpcp.test/awpcp/show-ads/?id=1
     *
     * TODO: Do this on Show Listings page only.
     * @since 4.0
     */
    public function maybe_set_current_post( $query ) {
        if ( ! $this->settings->get_option( 'display-listings-as-single-posts' ) ) {
            return;
        }

        if ( ! isset( $query->query_vars['id'] ) ) {
            return;
        }

        if ( preg_match( '/([0-9]+)/', $query->query_vars['id'], $matches ) ) {
            $query->query_vars['p'] = intval( $matches[1] );
            $query->query_vars['post_type'] = $this->post_type;
            unset( $query->query_vars[ 'pagename' ] );
        }
    }

    /**
     * @since 4.0
     */
    public function filter_post_type_link( $post_link, $post, $leavename, $sample ) {
        if ( $this->post_type != $post->post_type ) {
            return $post_link;
        }

        if ( ! get_option( 'permalink_structure' ) ) {
            $post_type_object = get_post_type_object( $this->post_type );

            if ( $post_type_object ) {
                $post_link = remove_query_arg( $post_type_object->query_var, $post_link );
            }

            $params = array(
                'page_id' => $this->get_show_listing_page_id(),
                'id' => $post->ID,
            );

            return add_query_arg( $params, $post_link );
        }

        if ( ! $this->settings->get_option( 'seofriendlyurls' ) ) {
            $rewrite_tags = array(
                '%awpcp_optional_listing_id%' => '',
            );

            $post_link = $this->replace_rewrite_tags( $rewrite_tags, $post_link );

            return add_query_arg( 'id', $post->ID, $post_link );
        }

        $rewrite_tags = array(
            '%awpcp_listing_id%' => $post->ID,
            '%awpcp_optional_listing_id%' => '',
            '%awpcp_category%' => strtolower( $this->listing_renderer->get_category_name( $post ) ),
            '%awpcp_location%' => $this->get_listing_location( $post ),
        );

        return $this->replace_rewrite_tags( $rewrite_tags, $post_link );
    }

    private function replace_rewrite_tags( $rewrite_tags, $post_link ) {
        $post_link = str_replace( array_keys( $rewrite_tags ), array_values( $rewrite_tags ), $post_link );
        $post_link = str_replace( ':!!', '://', str_replace( '//', '/', str_replace( '://', ':!!', $post_link ) ) );

        return $post_link;
    }

    /**
     * TODO: This method probably belongs somewhere else.
     *
     * @since 4.0
     */
    public function get_listing_location( $listing ) {
        $region = $this->listing_renderer->get_first_region( $listing );

        $parts = array();

        if( $this->settings->get_option( 'include-city-in-listing-url' ) && $region ) {
            $parts[] = sanitize_title( awpcp_array_data( 'city', '', $region ) );
        }
        if( $this->settings->get_option( 'include-state-in-listing-url' ) && $region ) {
            $parts[] = sanitize_title( awpcp_array_data( 'state', '', $region ) );
        }
        if( $this->settings->get_option( 'include-country-in-listing-url' ) && $region ) {
            $parts[] = sanitize_title( awpcp_array_data( 'country', '', $region ) );
        }
        if( $this->settings->get_option( 'include-county-in-listing-url' ) && $region ) {
            $parts[] = sanitize_title( awpcp_array_data( 'county', '', $region ) );
        }

        return strtolower( implode( '/', array_filter( $parts ) ) );
    }
}
