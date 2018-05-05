<?php
/**
 * @package AWPCP\Admim\Listings
 */

/**
 * Metabox to collect additional deatils for a classified.
 */
class AWPCP_ListingFieldsMetabox {

    /**
     * @var string
     */
    private $post_type;

    /**
     * @var object
     */
    private $listings_logic;

    /**
     * @var object
     */
    private $form_fields_data;

    /**
     * @var object
     */
    private $form_fields_validator;

    /**
     * @var object
     */
    private $form_fields;

    /**
     * @var object
     */
    private $date_form_fields;

    /**
     * @var object
     */
    private $template_renderer;

    /**
     * @var object
     */
    private $wordpress;

    /**
     * @param string $post_type                 The post type associated with this metabox.
     * @param object $listings_logic            An instance of Listings API.
     * @param object $form_fields_data          An instance of Form Fields Data.
     * @param object $form_fields_validator     An instance of Form Fields Validator.
     * @param object $form_fields               An instance of Details Form Fields.
     * @param object $date_form_fields          An instance of Date Form Fields.
     * @param object $template_renderer         An instance of Template Renderer.
     * @param object $wordpress                 An instance of WordPress.
     * @since 4.0.0
     */
    public function __construct( $post_type, $listings_logic, $form_fields_data, $form_fields_validator, $form_fields, $date_form_fields, $template_renderer, $wordpress ) {
        $this->post_type = $post_type;

        $this->listings_logic        = $listings_logic;
        $this->form_fields_data      = $form_fields_data;
        $this->form_fields_validator = $form_fields_validator;
        $this->form_fields           = $form_fields;
        $this->date_form_fields      = $date_form_fields;
        $this->template_renderer     = $template_renderer;
        $this->wordpress             = $wordpress;
    }

    /**
     * @since 4.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'awpcp-jquery-ui' );
        wp_enqueue_style( 'awpcp-admin-style' );

        wp_enqueue_script( 'awpcp-admin-edit-post' );

        // TODO: Inject JavaScript as a constructor parameter.
        awpcp()->js->localize( 'edit-post-form-fields', awpcp_listing_form_fields_validation_messages() );
    }

    /**
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     */
    public function render( $post ) {
        $data    = $this->form_fields_data->get_stored_data( $post );
        $errors  = array();
        $context = array(
            'category' => null,
            'action'   => 'normal',
        );

        $params = array(
            'details_form_fields' => $this->form_fields->render_fields( $data, $errors, $post, $context ),
            'date_form_fields'    => $this->date_form_fields->render_fields( $data, $errors, $post, $context ),
        );

        echo $this->template_renderer->render_template( 'admin/listings/listing-fields-metabox.tpl.php', $params ); // XSS Ok.
    }

    /**
     * TODO: Use redirect_post_location filter to add feedback messages on redirect_post().
     *
     * @param int    $post_id   The ID of the post being saved.
     * @param object $post      The post being saved.
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function save( $post_id, $post ) {
        if ( isset( $this->save_in_progress ) && $this->save_in_progress ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( $this->post_type !== $post->post_type ) {
            return;
        }

        if ( 'auto-draft' === $post->post_status ) {
            return;
        }

        $data   = $this->form_fields_data->get_posted_data( $post );
        $errors = $this->form_fields_validator->get_validation_errors( $data, $post );

        // Post Title and Content are handled by WordPress.
        unset( $data['post_fields']['post_title'] );
        unset( $data['post_fields']['post_content'] );
        unset( $errors['ad_title'] );
        unset( $errors['ad_details'] );

        $this->save_in_progress = true;

        $this->save_or_store_errors( $post, $data, $errors );

        $this->save_in_progress = false;
    }

    /**
     * @param object $post      An instance of WP Post.
     * @param array  $data      An array of data.
     * @param array  $errors    An array of errors.
     */
    private function save_or_store_errors( $post, $data, $errors ) {
        if ( empty( $errors ) ) {
            // TODO: Figure out the best place to calculate the number of regions allowed.
            $data['regions-allowed'] = 1;

            $this->listings_logic->update_listing( $post, $data );

            $this->wordpress->delete_post_meta( $post->ID, '__awpcp_admin_editor_pending_data' );
            $this->wordpress->delete_post_meta( $post->ID, '__awpcp_admin_editor_validation_errors' );

            return;
        }

        $this->wordpress->update_post_meta( $post->ID, '__awpcp_admin_editor_pending_data', $data );
        $this->wordpress->update_post_meta( $post->ID, '__awpcp_admin_editor_validation_errors', $errors );
    }
}
