<?php
/**
 * @package AWPCP\Admin\Listings
 */

/**
 * Handler for custom columns on Listings table.
 */
class AWPCP_ListingsTableColumnsHandler {

    /**
     * @var string
     */
    private $listing_category_taxonomy;

    /**
     * @var object
     */
    private $listing_renderer;

    /**
     * @var object
     */
    private $listings_collection;

    /**
     * @param string $listing_category_taxonomy     The name of the classifieds category taxonomy.
     * @param object $listing_renderer              An instance of Listing Renderer.
     * @param object $listings_collection           An instance of Listings Collection.
     * @since 4.0.0
     */
    public function __construct( $listing_category_taxonomy, $listing_renderer, $listings_collection ) {
        $this->listing_category_taxonomy = $listing_category_taxonomy;
        $this->listing_renderer          = $listing_renderer;
        $this->listings_collection       = $listings_collection;
    }

    /**
     * @param array $columns    An array of available columns.
     * @since 4.0.0
     */
    public function manage_posts_columns( $columns ) {
        $columns_keys   = array_keys( $columns );
        $columns_values = array_values( $columns );

        // Move Categories column.
        $position = array_search( 'taxonomy-' . $this->listing_category_taxonomy, $columns_keys, true );

        $categories_column_key   = array_splice( $columns_keys, $position, 1 );
        $categories_column_value = array_splice( $columns_values, $position, 1 );

        array_splice( $columns_keys, 2, 0, $categories_column_key );
        array_splice( $columns_values, 2, 0, $categories_column_value );

        // Add custom columns.
        $new_columns['awpcp-start-date']   = _x( 'Start Date', 'listings table column', 'another-wordpress-classifieds-plugin' );
        $new_columns['awpcp-end-date']     = _x( 'End Date', 'listings table column', 'another-wordpress-classifieds-plugin' );
        $new_columns['awpcp-renewed-date'] = _x( 'Renewed Date', 'listings table column', 'another-wordpress-classifieds-plugin' );
        $new_columns['awpcp-payment-term'] = _x( 'Payment Term', 'listings table column', 'another-wordpress-classifieds-plugin' );

        array_splice( $columns_keys, 3, 0, array_keys( $new_columns ) );
        array_splice( $columns_values, 3, 0, array_values( $new_columns ) );

        return array_combine( $columns_keys, $columns_values );
    }

    /**
     * @param string $column    The name of the column that is being rendered.
     * @param int    $post_id   The ID of the current post.
     * @since 4.0.0
     */
    public function manage_posts_custom_column( $column, $post_id ) {
        try {
            $post = $this->listings_collection->get( $post_id );
        } catch ( AWPCP_Exception $e ) {
            return;
        }

        switch ( $column ) {
            case 'awpcp-start-date':
                echo esc_html( $this->listing_renderer->get_start_date( $post ) );
                return;
            case 'awpcp-end-date':
                echo esc_html( $this->listing_renderer->get_end_date( $post ) );
                return;
            case 'awpcp-renewed-date':
                $renewed_date = $this->listing_renderer->get_renewed_date_formatted( $post );

                echo $renewed_date ? esc_html( $renewed_date ) : '---';
                return;
            case 'awpcp-payment-term':
                $payment_term = $this->listing_renderer->get_payment_term( $post );

                if ( ! $payment_term ) {
                    return;
                }

                echo esc_html( $payment_term->name );
        }
    }
}
