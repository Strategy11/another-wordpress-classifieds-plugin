<?php
/**
 * @package AWPCP\Tests\Plugin\Admin
 */

use Brain\Monkey\Functions;

/**
 * Tests for Admin Container Configuration.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AWPCP_AdminContainerConfigurationTest extends AWPCP_ContainerConfigurationTestCase {

    /**
     * Returns an array where every entry is an array with a class name and an
     * optional array of container-keys for that class' dependencies.
     *
     * @since 4.0.0
     */
    public function class_definitions_provider() {
        return [
            [ 'Admin' ],

            [ 'UnverifiedListingTableView' ],
            [ 'CompleteListingTableView' ],

            [ 'ListingsTableNavHandler' ],

            [ 'ListingsTableSearchHandler' ],
            [ 'KeywordListingsTableSearchMode' ],
            [ 'TitleListingsTableSearchMode' ],
            [ 'UserListingsTableSearchMode' ],
            [ 'ContactNameListingsTableSearchMode' ],
            [ 'ContactPhoneListingsTableSearchMode' ],
            [ 'PayerEmailListingsTableSearchMode' ],
            [ 'LocationListingsTableSearchMode' ],

            [ 'ListingsTableColumnsHandler' ],

            [ 'ListingFieldsMetabox' ],

            [ 'ToolsAdminPage' ],
        ];
    }

    /**
     * @since 4.0.0
     */
    protected function get_test_subject() {
        return new AWPCP_AdminContainerConfiguration();
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_listings_table_views_handler() {
        Functions\when( 'awpcp_request' )->justReturn( (object) [] );

        $this->test_class_definition(
            'ListingsTableViewsHandler',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingsTableViews'] = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_listings_table_views() {
        $this->test_class_definition(
            'ListingsTableViews',
            $this->get_test_subject()
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_new_listing_view() {
        $this->test_class_definition(
            'NewListingTableView',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingsCollection'] = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_expired_listing_view() {
        $this->test_class_definition(
            'ExpiredListingTableView',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingsCollection'] = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_awaiting_approval_listing_view() {
        $this->test_class_definition(
            'AwaitingApprovalListingTableView',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingsCollection'] = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_images_awaiting_approval_listing_view() {
        $this->test_class_definition(
            'ImagesAwaitingApprovalListingTableView',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingsCollection'] = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_flagged_listing_view() {
        $this->test_class_definition(
            'FlaggedListingTableView',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingsCollection'] = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_incomplete_listing_view() {
        $this->test_class_definition(
            'IncompleteListingTableView',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingsCollection'] = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_send_access_key_listing_action() {
        $this->test_class_definition(
            'SendAccessKeyListingTableAction',
            $this->get_test_subject(),
            function( $container ) {
                $container['EmailFactory']    = null;
                $container['ListingRenderer'] = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_mark_as_spam_listing_action() {
        $this->test_class_definition(
            'MarkAsSPAMListingTableAction',
            $this->get_test_subject(),
            function( $container ) {
                $container['SPAMSubmitter'] = null;
                $container['ListingsLogic'] = null;
                $container['WordPress']     = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_unflag_listing_action() {
        $this->test_class_definition(
            'UnflagListingTableAction',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingsLogic']   = null;
                $container['ListingRenderer'] = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_renew_listing_action() {
        $this->test_class_definition(
            'ModeratorRenewListingTableAction',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingsLogic']                    = null;
                $container['ListingRenderer']                  = null;
                $container['ListingRenewedEmailNotifications'] = null;
            }
        );

        $this->test_class_definition(
            'SubscriberRenewListingTableAction',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingRenderer']      = null;
                $container['RolesAndCapabilities'] = null;
            }
        );
    }

    /**
     * @since 4.0.0.
     */
    public function test_container_defines_make_featured_listing_action() {
        $this->test_class_definition(
            'MakeFeaturedListingTableAction',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingRenderer'] = null;
                $container['WordPress']       = null;
            }
        );
    }

    /**
     * @since 4.0.0.
     */
    public function test_container_defines_make_standard_listing_action() {
        $this->test_class_definition(
            'MakeStandardListingTableAction',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingRenderer'] = null;
                $container['WordPress']       = null;
            }
        );
    }

    /**
     * @since 4.0.0.
     */
    public function test_container_defines_mark_reviewed_listing_action() {
        $this->test_class_definition(
            'MarkReviewedListingTableAction',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingRenderer'] = null;
                $container['WordPress']       = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_send_to_facebook_page_listing_action() {
        $this->test_class_definition(
            'SendToFacebookPageListingTableAction',
            $this->get_test_subject(),
            function( $container ) {
                $container['SendListingToFacebookHelper'] = null;
                $container['WordPress']                   = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_container_defines_send_to_facebook_group_listing_action() {
        $this->test_class_definition(
            'SendToFacebookGroupListingTableAction',
            $this->get_test_subject(),
            function( $container ) {
                $container['SendListingToFacebookHelper'] = null;
                $container['WordPress']                   = null;
            }
        );
    }
}
