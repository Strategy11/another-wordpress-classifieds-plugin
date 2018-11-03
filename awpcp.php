<?php
/**
 * Plugin Name: Another WordPress Classifieds Plugin (AWPCP)
 * Plugin URI: http://www.awpcp.com
 * Description: AWPCP - A plugin that provides the ability to run a free or paid classified ads service on your WP site. <strong>!!!IMPORTANT!!!</strong> It's always a good idea to do a BACKUP before you upgrade AWPCP!
 * Version: 4.0.0beta4
 * Author: D. Rodenbaugh
 * License: GPLv2 or any later version
 * Author URI: http://www.skylineconsult.com
 * Text Domain: another-wordpress-classifieds-plugin
 * Domain Path: /languages
 *
 * @package AWPCP
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * dcfunctions.php and filop.class.php used with permission of Dan Caragea, http://datemill.com
 * AWPCP Classifieds icon set courtesy of http://www.famfamfam.com/lab/icons/silk/
 */

// phpcs:disable Generic
// phpcs:disable PEAR
// phpcs:disable PSR2
// phpcs:disable Squiz
// phpcs:disable WordPress

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
}

define( 'AWPCP_BASENAME', basename( dirname( __FILE__ ) ) );
define( 'AWPCP_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'AWPCP_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

define( 'AWPCP_LOWEST_FILTER_PRIORITY', 1000000 );

define( 'AWPCP_LISTING_POST_TYPE', 'awpcp_listing' );
define( 'AWPCP_CATEGORY_TAXONOMY', 'awpcp_listing_category' );

global $awpcp;

global $awpcp_db_version;

global $wpcontenturl;
global $wpcontentdir;
global $awpcp_plugin_path;
global $awpcp_plugin_url;
global $imagespath;
global $awpcp_imagesurl;

global $nameofsite;

$awpcp_db_version = '4.0.0beta4';

$wpcontenturl = WP_CONTENT_URL;
$wpcontentdir = WP_CONTENT_DIR;
$awpcp_plugin_path = AWPCP_DIR;
$awpcp_plugin_url = AWPCP_URL;
$imagespath = $awpcp_plugin_path . '/resources/images';
$awpcp_imagesurl = $awpcp_plugin_url .'/resources/images';

// TODO: verify we are running on PHP 5.3 or superior
require_once( AWPCP_DIR . '/vendor/autoload.php' );

// XXX: Required because Settings API attempts to use register_setting on
//      every request.
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// common
require_once(AWPCP_DIR . "/debug.php");
require_once(AWPCP_DIR . "/functions.php");
require( AWPCP_DIR . "/includes/functions/compat.php" );
require_once( AWPCP_DIR . "/includes/functions/categories.php" );
require_once( AWPCP_DIR . "/includes/functions/deprecated.php" );
require( AWPCP_DIR . '/includes/functions/file-upload.php' );
require_once( AWPCP_DIR . "/includes/functions/format.php" );
require_once( AWPCP_DIR . "/includes/functions/hooks.php" );
require_once( AWPCP_DIR . "/includes/functions/l10n.php" );
require_once( AWPCP_DIR . "/includes/functions/listings.php" );
require_once( AWPCP_DIR . "/includes/functions/notifications.php" );
require_once( AWPCP_DIR . "/includes/functions/payments.php" );
require_once( AWPCP_DIR . "/includes/functions/routes.php" );
require( AWPCP_DIR . "/includes/functions/legacy.php" );

$nameofsite = awpcp_get_blog_name();

// cron
require_once(AWPCP_DIR . "/cron.php");

// API & Classes
require_once(AWPCP_DIR . "/includes/exceptions.php");

require_once AWPCP_DIR . '/includes/admin/interface-personal-data-provider.php';
require_once AWPCP_DIR . '/includes/admin/class-data-formatter.php';
require_once AWPCP_DIR . '/includes/admin/class-listings-personal-data-provider.php';
require_once AWPCP_DIR . '/includes/admin/class-payment-personal-data-provider.php';
require_once AWPCP_DIR . '/includes/admin/class-personal-data-exporter.php';
require_once AWPCP_DIR . '/includes/admin/class-personal-data-eraser.php';
require_once AWPCP_DIR . '/includes/admin/class-privacy-policy-content.php';
require_once AWPCP_DIR . '/includes/admin/class-user-personal-data-provider.php';

require( AWPCP_DIR . "/includes/interface-posts-meta-configuration.php" );

require_once(AWPCP_DIR . "/includes/compatibility/compatibility.php");
require_once( AWPCP_DIR . '/includes/compatibility/interface-plugin-integration.php' );
require_once( AWPCP_DIR . "/includes/compatibility/class-add-meta-tags-plugin-integration.php" );
require_once(AWPCP_DIR . "/includes/compatibility/class-all-in-one-seo-pack-plugin-integration.php");
require_once AWPCP_DIR . '/includes/compatibility/class-complete-open-graph-plugin-integration.php';
require( AWPCP_DIR . "/includes/compatibility/class-facebook-button-plugin-integration.php");
require_once(AWPCP_DIR . "/includes/compatibility/class-facebook-plugin-integration.php");
require_once( AWPCP_DIR . '/includes/compatibility/class-facebook-all-plugin-integration.php' );
require_once( AWPCP_DIR . "/includes/compatibility/class-jetpack-plugin-integration.php" );
require_once( AWPCP_DIR . '/includes/compatibility/class-mashshare-plugin-integration.php' );
require_once( AWPCP_DIR . '/includes/compatibility/class-plugin-integrations.php' );
require( AWPCP_DIR . "/includes/compatibility/class-profile-builder-plugin-integration.php");
require( AWPCP_DIR . "/includes/compatibility/class-profile-builder-login-form-implementation.php");
require( AWPCP_DIR . '/includes/compatibility/class-simple-facebook-opengrap-tags-plugin-integration.php' );
require_once( AWPCP_DIR . "/includes/compatibility/class-woocommerce-plugin-integration.php" );
require( AWPCP_DIR . "/includes/compatibility/class-wp-members-login-form-implementation.php");
require( AWPCP_DIR . "/includes/compatibility/class-wp-members-plugin-integration.php");
require_once( AWPCP_DIR . "/includes/compatibility/class-yoast-wordpress-seo-plugin-integration.php" );

require_once( AWPCP_DIR . "/includes/functions/settings.php" );

require_once( AWPCP_DIR . "/includes/form-fields/class-form-field.php" );
require_once( AWPCP_DIR . "/includes/form-fields/class-form-fields.php" );
require_once( AWPCP_DIR . "/includes/form-fields/class-listing-form-fields.php" );
require_once( AWPCP_DIR . "/includes/form-fields/class-listing-contact-name-form-field.php" );
require_once( AWPCP_DIR . "/includes/form-fields/class-listing-contact-email-form-field.php" );
require_once( AWPCP_DIR . "/includes/form-fields/class-listing-contact-phone-form-field.php" );
require_once( AWPCP_DIR . "/includes/form-fields/class-listing-details-form-field.php" );
require_once( AWPCP_DIR . "/includes/form-fields/class-listing-price-form-field.php" );
require_once( AWPCP_DIR . "/includes/form-fields/class-listing-regions-form-field.php" );
require_once( AWPCP_DIR . "/includes/form-fields/class-listing-title-form-field.php" );
require_once( AWPCP_DIR . "/includes/form-fields/class-listing-website-form-field.php" );

require_once( AWPCP_DIR . "/includes/helpers/class-easy-digital-downloads.php" );
require_once( AWPCP_DIR . "/includes/helpers/class-licenses-manager.php" );
require_once( AWPCP_DIR . '/includes/helpers/class-module.php' );
require( AWPCP_DIR . "/includes/helpers/class-modules-manager-factory.php" );
require_once( AWPCP_DIR . "/includes/helpers/class-modules-manager.php" );
require_once( AWPCP_DIR . "/includes/helpers/class-modules-updater.php" );

require_once( AWPCP_DIR . '/includes/helpers/class-admin-page-links-builder.php' );
require_once(AWPCP_DIR . "/includes/helpers/class-akismet-wrapper-base.php");
require_once(AWPCP_DIR . "/includes/helpers/class-akismet-wrapper.php");
require_once(AWPCP_DIR . "/includes/helpers/class-akismet-wrapper-factory.php");
require_once(AWPCP_DIR . "/includes/helpers/class-awpcp-request.php");
require_once( AWPCP_DIR . '/includes/helpers/class-facebook-cache-helper.php' );
require_once(AWPCP_DIR . "/includes/helpers/class-file-cache.php");
require_once( AWPCP_DIR . "/includes/helpers/class-http.php" );
require_once( AWPCP_DIR . "/includes/helpers/class-listing-akismet-data-source.php" );
require_once( AWPCP_DIR . "/includes/helpers/class-listing-renderer.php" );
require_once( AWPCP_DIR . "/includes/helpers/class-listing-reply-akismet-data-source.php" );
require_once( AWPCP_DIR . "/includes/helpers/class-page-title-builder.php" );
require_once( AWPCP_DIR . "/includes/helpers/class-payment-transaction-helper.php" );
require_once( AWPCP_DIR . "/includes/helpers/class-send-to-facebook-helper.php" );
require_once( AWPCP_DIR . "/includes/helpers/class-spam-filter.php" );
require_once( AWPCP_DIR . "/includes/helpers/class-spam-submitter.php" );
require_once( AWPCP_DIR . '/includes/helpers/facebook.php' );
require_once(AWPCP_DIR . "/includes/helpers/list-table.php");
require_once(AWPCP_DIR . "/includes/helpers/email.php");
require_once(AWPCP_DIR . "/includes/helpers/javascript.php");
require_once(AWPCP_DIR . "/includes/helpers/widgets/multiple-region-selector.php");
require_once(AWPCP_DIR . "/includes/helpers/widgets/class-asynchronous-tasks-component.php");
require_once(AWPCP_DIR . "/includes/helpers/widgets/class-listing-actions-component.php");
require_once(AWPCP_DIR . "/includes/helpers/widgets/class-user-field.php");
require_once(AWPCP_DIR . "/includes/helpers/widgets/class-users-dropdown.php");
require_once(AWPCP_DIR . "/includes/helpers/widgets/class-users-autocomplete.php");

require( AWPCP_DIR . '/includes/html/interface-html-element.php' );
require( AWPCP_DIR . '/includes/html/interface-html-element-renderer.php' );
require( AWPCP_DIR . '/includes/html/class-html-renderer.php' );
require( AWPCP_DIR . '/includes/html/class-html-default-element-renderer.php' );

require_once AWPCP_DIR . '/includes/integrations/facebook/class-facebook-integration.php';

require( AWPCP_DIR . "/includes/listings/class-listings-meta-configuration.php" );
require_once( AWPCP_DIR . "/includes/listings/class-listing-action.php" );
require_once( AWPCP_DIR . "/includes/listings/class-listing-action-with-confirmation.php" );
require_once( AWPCP_DIR . "/includes/listings/class-delete-listing-action.php" );

require_once( AWPCP_DIR . "/includes/meta/class-meta-tags-generator.php" );
require_once( AWPCP_DIR . "/includes/meta/class-tag-renderer.php" );

require( AWPCP_DIR . "/includes/models/class-custom-post-types.php" );
require_once(AWPCP_DIR . "/includes/models/payment-transaction.php");

require_once( AWPCP_DIR . "/includes/db/class-database-column-creator.php" );
require( AWPCP_DIR . "/includes/db/class-database-helper.php" );

require( AWPCP_DIR . "/includes/fees/class-fees-collection.php" );

require( AWPCP_DIR . "/includes/ui/class-categories-selector-helper.php" );
require( AWPCP_DIR . "/includes/ui/class-payment-terms-list.php" );
require( AWPCP_DIR . "/includes/ui/class-category-selector.php" );

require( AWPCP_DIR . '/includes/ui/class-classifieds-bar.php' );
require_once AWPCP_DIR . "/includes/ui/class-form-steps-component.php";
require( AWPCP_DIR . '/includes/ui/class-classifieds-search-bar-component.php' );
require( AWPCP_DIR . '/includes/ui/class-classifieds-menu-component.php' );

require_once( AWPCP_DIR . "/includes/views/class-ajax-handler.php" );
require_once( AWPCP_DIR . "/includes/views/class-base-page.php" );
require_once( AWPCP_DIR . "/includes/views/class-step-decorator.php" );
require_once( AWPCP_DIR . "/includes/views/class-payment-step-decorator.php" );
require_once( AWPCP_DIR . "/includes/views/class-prepare-transaction-for-payment-step-decorator.php" );
require_once( AWPCP_DIR . "/includes/views/class-set-credit-plan-step-decorator.php" );
require_once( AWPCP_DIR . "/includes/views/class-set-payment-method-step-decorator.php" );
require_once( AWPCP_DIR . "/includes/views/class-set-transaction-status-to-open-step-decorator.php" );
require_once( AWPCP_DIR . "/includes/views/class-set-transaction-status-to-checkout-step-decorator.php" );
require_once( AWPCP_DIR . "/includes/views/class-set-transaction-status-to-completed-step-decorator.php" );
require_once( AWPCP_DIR . "/includes/views/class-skip-payment-step-if-payment-is-not-required.php" );
require_once( AWPCP_DIR . "/includes/views/class-users-autocomplete-ajax-handler.php" );
require_once( AWPCP_DIR . "/includes/views/class-verify-credit-plan-was-set-step-decorator.php" );
require_once( AWPCP_DIR . "/includes/views/class-verify-payment-can-be-processed-step-decorator.php" );
require_once( AWPCP_DIR . "/includes/views/class-verify-transaction-exists-step-decorator.php" );
// load frontend views first, some frontend pages are required in admin pages
require_once( AWPCP_DIR . '/includes/views/frontend/buy-credits/class-buy-credits-page.php');
require_once( AWPCP_DIR . "/includes/views/frontend/buy-credits/class-buy-credits-page-select-credit-plan-step.php" );
require_once( AWPCP_DIR . "/includes/views/frontend/buy-credits/class-buy-credits-page-checkout-step.php" );
require_once( AWPCP_DIR . "/includes/views/frontend/buy-credits/class-buy-credits-page-payment-completed-step.php" );
require_once( AWPCP_DIR . "/includes/views/frontend/buy-credits/class-buy-credits-page-final-step.php" );
require_once( AWPCP_DIR . "/includes/views/frontend/class-categories-list-walker.php" );
require_once( AWPCP_DIR . "/includes/views/frontend/class-categories-renderer.php" );
require_once( AWPCP_DIR . "/includes/views/frontend/class-category-shortcode.php" );
require_once( AWPCP_DIR . "/includes/views/admin/class-fee-payment-terms-notices.php" );
require_once( AWPCP_DIR . "/includes/views/admin/class-credit-plans-notices.php" );
require_once( AWPCP_DIR . "/includes/views/admin/class-categories-checkbox-list-walker.php" );
require_once( AWPCP_DIR . "/includes/views/admin/listings/class-listing-action-admin-page.php" );
require_once( AWPCP_DIR . "/includes/views/admin/listings/class-renew-listings-admin-page.php" );
require_once( AWPCP_DIR . "/includes/views/admin/listings/class-listings-table-search-by-id-condition.php" );
require_once( AWPCP_DIR . "/includes/views/admin/listings/class-listings-table-search-by-keyword-condition.php" );
require_once( AWPCP_DIR . "/includes/views/admin/listings/class-listings-table-search-by-location-condition.php" );
require_once( AWPCP_DIR . "/includes/views/admin/listings/class-listings-table-search-by-payer-email-condition.php" );
require_once( AWPCP_DIR . "/includes/views/admin/listings/class-listings-table-search-by-title-condition.php" );
require_once( AWPCP_DIR . "/includes/views/admin/listings/class-listings-table-search-by-user-condition.php" );
require_once( AWPCP_DIR . "/includes/views/admin/account-balance/class-account-balance-page.php" );
require_once( AWPCP_DIR . "/includes/views/admin/account-balance/class-account-balance-page-summary-step.php" );

require_once( AWPCP_DIR . '/includes/cron/class-task-queue.php' );
require_once( AWPCP_DIR . '/includes/cron/class-task-logic-factory.php' );
require_once( AWPCP_DIR . '/includes/cron/class-task-logic.php' );
require_once( AWPCP_DIR . '/includes/cron/class-tasks-collection.php' );
require_once( AWPCP_DIR . '/includes/cron/class-background-process.php' );

require_once( AWPCP_DIR . '/includes/media/class-listing-file-validator.php' );

require( AWPCP_DIR . '/includes/media/interface-attachment-ajax-action.php' );
require( AWPCP_DIR . '/includes/media/class-attachments-collection.php' );
require( AWPCP_DIR . '/includes/media/class-attachments-logic.php' );
require( AWPCP_DIR . "/includes/media/class-attachment-action-ajax-handler.php" );
require( AWPCP_DIR . '/includes/media/class-attachment-properties.php' );
require( AWPCP_DIR . '/includes/media/class-attachment-status.php' );
require( AWPCP_DIR . '/includes/media/class-delete-attachment-ajax-action.php' );
require( AWPCP_DIR . '/includes/media/class-file-handlers-manager.php' );
require_once( AWPCP_DIR . '/includes/media/class-file-types.php' );
require_once( AWPCP_DIR . '/includes/media/class-file-uploader.php' );
require_once( AWPCP_DIR . '/includes/media/class-file-validation-errors.php' );
require_once( AWPCP_DIR . '/includes/media/class-filesystem.php' );
require( AWPCP_DIR . '/includes/media/class-image-attachment-creator.php' );
require_once( AWPCP_DIR . '/includes/media/class-image-file-processor.php' );
require_once( AWPCP_DIR . '/includes/media/class-image-file-validator.php' );
require_once( AWPCP_DIR . '/includes/media/class-image-resizer.php' );
require( AWPCP_DIR . '/includes/media/class-listings-media-uploader-component.php' );
require( AWPCP_DIR . '/includes/media/class-listing-attachment-creator.php' );
require( AWPCP_DIR . '/includes/media/class-listing-file-handler.php' );
require_once( AWPCP_DIR . '/includes/media/class-listing-upload-limits.php' );
require_once( AWPCP_DIR . "/includes/media/class-media-manager-component.php" );
require_once( AWPCP_DIR . "/includes/media/class-media-manager.php" );
require_once( AWPCP_DIR . '/includes/media/class-media-uploaded-notification.php' );
require_once( AWPCP_DIR . '/includes/media/class-media-uploader-component.php' );
require_once( AWPCP_DIR . "/includes/media/class-messages-component.php" );
require_once( AWPCP_DIR . '/includes/media/class-mime-types.php' );
require( AWPCP_DIR . '/includes/media/class-set-attachment-as-featured-ajax-action.php' );
require( AWPCP_DIR . '/includes/media/class-update-attachment-allowed-status-ajax-action.php' );
require( AWPCP_DIR . '/includes/media/class-update-attachment-enabled-status-ajax-action.php' );
require_once( AWPCP_DIR . '/includes/media/class-uploaded-file-logic-factory.php' );
require_once( AWPCP_DIR . '/includes/media/class-uploaded-file-logic.php' );
require_once( AWPCP_DIR . '/includes/media/class-upload-listing-media-ajax-handler.php' );
require_once( AWPCP_DIR . '/includes/media/class-upload-generated-thumbnail-ajax-handler.php' );

require( AWPCP_DIR . "/includes/modules/class-license-settings-update-handler.php" );
require( AWPCP_DIR . "/includes/modules/class-license-settings-actions-request-handler.php" );

require_once( AWPCP_DIR . '/includes/placeholders/class-placeholders-installation-verifier.php' );

require_once( AWPCP_DIR . '/includes/routes/class-ajax-request-handler.php' );
require_once( AWPCP_DIR . '/includes/routes/class-router.php' );
require_once( AWPCP_DIR . '/includes/routes/class-routes.php' );

require_once( AWPCP_DIR . "/includes/settings/class-files-settings.php" );
require_once( AWPCP_DIR . "/includes/settings/class-general-settings.php" );
require_once( AWPCP_DIR . "/includes/settings/class-listings-moderation-settings.php" );
require_once( AWPCP_DIR . "/includes/settings/class-user-notifications-settings.php" );

require( AWPCP_DIR . "/includes/upgrade/interface-upgrade-task-runner.php" );

require( AWPCP_DIR . "/includes/upgrade/class-categories-registry.php" );

require( AWPCP_DIR . "/includes/upgrade/class-update-categories-task-runner.php" );
require( AWPCP_DIR . "/includes/upgrade/class-upgrade-task-handler.php" );
require( AWPCP_DIR . "/includes/upgrade/class-database-tables.php" );
require_once( AWPCP_DIR . "/includes/upgrade/class-manual-upgrade-tasks.php" );
require_once( AWPCP_DIR . "/includes/upgrade/class-upgrade-tasks-manager.php" );
require_once( AWPCP_DIR . "/includes/upgrade/class-upgrade-task-ajax-handler.php" );

require_once( AWPCP_DIR . "/includes/upgrade/class-migrate-regions-information-task-handler.php" );

require_once( AWPCP_DIR . "/includes/wordpress/class-wordpress-scripts.php" );
require_once( AWPCP_DIR . "/includes/wordpress/class-wordpress.php" );

require( AWPCP_DIR . '/includes/class-authentication-redirection-handler.php' );
require( AWPCP_DIR . '/includes/class-browse-categories-page-redirection-handler.php' );
require_once( AWPCP_DIR . '/includes/class-edit-listing-url-placeholder.php' );
require_once( AWPCP_DIR . '/includes/class-edit-listing-link-placeholder.php' );

require_once( AWPCP_DIR . "/includes/class-listings-api.php" );
require_once( AWPCP_DIR . "/includes/class-cookie-manager.php" );
require( AWPCP_DIR . "/includes/class-categories-collection.php" );
require( AWPCP_DIR . '/includes/class-categories-renderer-data-provider.php' );
require( AWPCP_DIR . '/includes/class-default-login-form-implementation.php' );
require( AWPCP_DIR . "/includes/categories/class-categories-logic.php" );
require_once( AWPCP_DIR . "/includes/class-exceptions.php" );
require( AWPCP_DIR . "/includes/class-legacy-listings-metadata.php" );
require_once( AWPCP_DIR . "/includes/class-listing-authorization.php" );
require_once( AWPCP_DIR . "/includes/class-listing-payment-transaction-handler.php" );
require_once( AWPCP_DIR . "/includes/class-renew-listing-payment-transaction-handler.php" );
require_once( AWPCP_DIR . "/includes/class-listing-is-about-to-expire-notification.php" );
require_once( AWPCP_DIR . "/includes/class-listings-collection.php" );
require_once( AWPCP_DIR . "/includes/class-missing-pages-finder.php" );
require_once( AWPCP_DIR . "/includes/class-pages-creator.php" );
require( AWPCP_DIR . '/includes/class-plugin-rewrite-rules.php' );
require( AWPCP_DIR . '/includes/class-posts-meta.php' );
require( AWPCP_DIR . '/includes/class-rewrite-rules-helper.php' );
require_once( AWPCP_DIR . "/includes/class-roles-and-capabilities.php" );
require_once( AWPCP_DIR . "/includes/class-secure-url-redirection-handler.php" );
require( AWPCP_DIR . '/includes/class-settings-json-reader.php' );
require( AWPCP_DIR . '/includes/class-settings-json-writer.php' );
require( AWPCP_DIR . '/includes/class-template-renderer.php' );
require_once( AWPCP_DIR . "/includes/class-users-collection.php" );
require_once(AWPCP_DIR . "/includes/payments-api.php");
require_once(AWPCP_DIR . "/includes/regions-api.php");
require_once(AWPCP_DIR . "/includes/settings-api.php");

require_once(AWPCP_DIR . "/includes/credit-plan.php");

require_once(AWPCP_DIR . "/includes/payment-term-type.php");
require_once(AWPCP_DIR . "/includes/payment-term.php");
require_once(AWPCP_DIR . "/includes/payment-term-fee-type.php");
require_once(AWPCP_DIR . "/includes/payment-term-fee.php");

require_once(AWPCP_DIR . "/includes/payment-gateway.php");
require_once(AWPCP_DIR . "/includes/payment-gateway-paypal-standard.php");
require_once(AWPCP_DIR . "/includes/payment-gateway-2checkout.php");

require_once(AWPCP_DIR . "/includes/payment-terms-table.php");

// installation functions
require( AWPCP_DIR . "/installer.php" );

// admin functions
require( AWPCP_DIR . '/admin/interface-table-entry-action-handler.php' );
require_once(AWPCP_DIR . "/admin/admin-panel.php");
require_once( AWPCP_DIR . '/admin/class-delete-browse-categories-page-notice.php' );
require_once( AWPCP_DIR . '/admin/class-dismiss-notice-ajax-handler.php' );
require( AWPCP_DIR . '/admin/class-export-settings-admin-page.php' );
require( AWPCP_DIR . '/admin/class-import-settings-admin-page.php' );
require_once( AWPCP_DIR . '/admin/class-missing-paypal-merchant-id-setting-notice.php' );
require_once( AWPCP_DIR . '/admin/class-admin-menu-builder.php' );
require( AWPCP_DIR . "/admin/class-admin-page-url-builder.php");
require_once( AWPCP_DIR . '/admin/class-categories-admin-page.php' );
require( AWPCP_DIR . '/admin/class-import-listings-admin-page.php' );
require( AWPCP_DIR . '/admin/class-debug-admin-page.php' );
require_once( AWPCP_DIR . '/admin/class-main-classifieds-admin-page.php' );
require_once( AWPCP_DIR . '/admin/class-settings-admin-page.php' );
require_once( AWPCP_DIR . '/admin/class-table-entry-action-ajax-handler.php' );
require( AWPCP_DIR . '/admin/class-uninstall-admin-page.php' );
require( AWPCP_DIR . '/admin/class-add-edit-table-entry-rendering-helper.php' );

require( AWPCP_DIR . '/admin/categories/class-create-category-admin-page.php' );
require( AWPCP_DIR . '/admin/categories/class-delete-categories-admin-page.php' );
require( AWPCP_DIR . '/admin/categories/class-delete-category-admin-page.php' );
require( AWPCP_DIR . '/admin/categories/class-move-categories-admin-page.php' );
require( AWPCP_DIR . '/admin/categories/class-update-category-admin-page.php' );

require_once( AWPCP_DIR . '/admin/credit-plans/class-credit-plans-admin-page.php' );
require_once( AWPCP_DIR . '/admin/credit-plans/class-add-credit-plan-action-handler.php' );
require_once( AWPCP_DIR . '/admin/credit-plans/class-delete-credit-plan-action-handler.php' );
require_once( AWPCP_DIR . '/admin/credit-plans/class-edit-credit-plan-action-handler.php' );
require_once( AWPCP_DIR . '/admin/fees/class-delete-fee-action-handler.php' );
require_once( AWPCP_DIR . '/admin/fees/class-fees-admin-page.php' );
require( AWPCP_DIR . '/admin/fees/class-fee-details-admin-page.php' );
require( AWPCP_DIR . '/admin/fees/class-fee-details-form.php' );
require_once( AWPCP_DIR . '/admin/listings/class-delete-listing-ajax-handler.php' );
require_once( AWPCP_DIR . '/admin/pointers/class-drip-autoresponder-ajax-handler.php' );
require_once( AWPCP_DIR . '/admin/pointers/class-drip-autoresponder.php' );
require_once( AWPCP_DIR . '/admin/pointers/class-pointers-manager.php' );
require_once( AWPCP_DIR . '/admin/profile/class-user-profile-contact-information-controller.php' );
require_once( AWPCP_DIR . '/admin/form-fields/class-form-fields-admin-page.php' );
require_once( AWPCP_DIR . '/admin/form-fields/class-form-fields-table-factory.php' );
require_once( AWPCP_DIR . '/admin/form-fields/class-form-fields-table.php' );
require_once( AWPCP_DIR . '/admin/form-fields/class-update-form-fields-order-ajax-handler.php' );
require_once( AWPCP_DIR . '/admin/import/class-csv-import-session.php' );
require_once( AWPCP_DIR . '/admin/import/class-csv-import-sessions-manager.php' );
require_once( AWPCP_DIR . '/admin/import/class-csv-importer.php' );
require_once( AWPCP_DIR . '/admin/import/class-csv-importer-factory.php' );
require_once( AWPCP_DIR . '/admin/import/class-csv-importer-delegate.php' );
require_once( AWPCP_DIR . '/admin/import/class-csv-importer-delegate-factory.php' );
require_once( AWPCP_DIR . '/admin/import/class-csv-reader-factory.php' );
require_once( AWPCP_DIR . '/admin/import/class-csv-reader.php' );
require_once( AWPCP_DIR . '/admin/import/class-import-listings-ajax-handler.php' );
require_once( AWPCP_DIR . "/admin/upgrade/class-manual-upgrade-admin-page.php" );
require_once( AWPCP_DIR . '/admin/user-panel.php' );

// required later to make sure dependencies are already loaded
require_once(AWPCP_DIR . "/admin/user-panel.php");

// frontend functions
require_once(AWPCP_DIR . "/frontend/placeholders.php");
require_once(AWPCP_DIR . "/frontend/ad-functions.php");
require_once(AWPCP_DIR . "/frontend/shortcode.php");

require( AWPCP_DIR . '/frontend/class-categories-renderer-factory.php' );
require( AWPCP_DIR . '/frontend/class-categories-switcher.php' );
require( AWPCP_DIR . '/frontend/class-image-placeholders.php' );
require( AWPCP_DIR . '/frontend/class-loop-integration.php' );
require( AWPCP_DIR . '/frontend/class-query.php' );
require( AWPCP_DIR . '/frontend/class-url-backwards-compatibility-redirection-helper.php' );
require_once(AWPCP_DIR . "/frontend/widget-search.php");
require_once(AWPCP_DIR . "/frontend/widget-latest-ads.php");
require_once(AWPCP_DIR . "/frontend/widget-random-ad.php");
require_once(AWPCP_DIR . "/frontend/widget-categories.php");
require( AWPCP_DIR . '/frontend/class-wordpress-status-header-filter.php' );

require_once AWPCP_DIR . '/includes/class-awpcp.php';

/**
 * TODO: Remove this function. We have autoload now.
 */
function awpcp_container() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $instance = new AWPCP_Container( [
            'plugin_basename' => plugin_basename( __FILE__ ),
            'SettingsManager' => new AWPCP_SettingsManager(),
        ] );
    }

    return $instance;
}

function awpcp() {
	global $awpcp;

	if (!is_object($awpcp)) {
        $container = awpcp_container();

        include( AWPCP_DIR . '/includes/constructor-functions.php' );

        $container['Plugin'] = new AWPCP( $container );

        $awpcp = $container['Plugin'];
        $awpcp->bootstrap();
	}

	return $awpcp;
}

awpcp();

define('MENUICO', $awpcp_imagesurl .'/menuico.png');

global $hascaticonsmodule;
global $hasregionsmodule;
global $haspoweredbyremovalmodule;
global $hasgooglecheckoutmodule;
global $hasextrafieldsmodule;
global $hasrssmodule;
global $hasfeaturedadsmodule;

$hasextrafieldsmodule = $hasextrafieldsmodule ? true : false;
$hasregionsmodule = $hasregionsmodule ? true : false;
$hasfeaturedadsmodule = $hasfeaturedadsmodule ? true : false;
$hasrssmodule = $hasrssmodule ? true : false;

$hascaticonsmodule = 0;
$haspoweredbyremovalmodule = 0;
$hasgooglecheckoutmodule = 0;

if (!defined('AWPCP_REGION_CONTROL_MODULE') && file_exists(AWPCP_DIR . "/awpcp_region_control_module.php")) {
	require_once(AWPCP_DIR . "/awpcp_region_control_module.php");
	$hasregionsmodule = true;
}

if (!defined('AWPCP_EXTRA_FIELDS_MODULE') && file_exists(AWPCP_DIR . "/awpcp_extra_fields_module.php")) {
	require_once(AWPCP_DIR . "/awpcp_extra_fields_module.php");
	$hasextrafieldsmodule = true;
}

if (!defined('AWPCP_RSS_MODULE') && file_exists(AWPCP_DIR . "/awpcp_rss_module.php")) {
	require_once(AWPCP_DIR . "/awpcp_rss_module.php");
	$hasrssmodule = true;
}

if (!defined('AWPCP_GOOGLE_CHECKOUT_MODULE') && file_exists(AWPCP_DIR . "/awpcp_google_checkout_module.php")) {
	require_once(AWPCP_DIR . "/awpcp_google_checkout_module.php");
	$hasgooglecheckoutmodule = true;
}

if (file_exists(AWPCP_DIR . "/awpcp_category_icons_module.php")) {
	require_once(AWPCP_DIR . "/awpcp_category_icons_module.php");
	$hascaticonsmodule=1;
}

if (file_exists(AWPCP_DIR . "/awpcp_remove_powered_by_module.php")) {
	require_once(AWPCP_DIR . "/awpcp_remove_powered_by_module.php");
	$haspoweredbyremovalmodule=1;
}


/**
 * Returns the IDs of the pages used by the AWPCP plugin.
 *
 * @SuppressWarnings(PHPMD)
 */
function exclude_awpcp_child_pages($excluded=array()) {
	global $wpdb, $table_prefix;

	$awpcp_page_id = awpcp_get_page_id_by_ref('main-page-name');

	if (empty($awpcp_page_id)) {
		return array();
	}

	$query = "SELECT ID FROM {$table_prefix}posts ";
	$query.= "WHERE post_parent=$awpcp_page_id AND post_content LIKE '%AWPCP%'";

	$child_pages = $wpdb->get_col( $query );

	if ( is_array( $child_pages ) ) {
		return array_merge( $child_pages, $excluded );
	} else {
		return $excluded;
	}
}



// PROGRAM FUNCTIONS

/**
 * Return an array of refnames for pages associated with one or more
 * rewrite rules.
 *
 * @since 2.1.3
 * @return array Array of page refnames.
 */
function awpcp_pages_with_rewrite_rules() {
	return array(
		'main-page-name',
		'show-ads-page-name',
		'reply-to-ad-page-name',
        'edit-ad-page-name',
		'browse-ads-page-name',
	);
}

/**
 * Register AWPCP query vars
 */
function awpcp_query_vars($query_vars) {
	$vars = array(
		// API
		'awpcpx',
		'awpcp-module',
		'awpcp-action',
		'module',
		'action',

		// Payments API
		'awpcp-txn',

		// Listings API
		'awpcp-ad',
		'awpcp-hash',

		// misc
        'awpcp-custom',
		"cid",
		"id",
		"layout",
		"regionid",
	);

	return array_merge($query_vars, $vars);
}

/**
 * @since 3.2.1
 * @SuppressWarnings(PHPMD)
 */
function awpcp_rel_canonical_url() {
	global $wp_the_query;

	if ( ! is_singular() )
		return false;

	if ( ! $page_id = $wp_the_query->get_queried_object_id() ) {
		return false;
	}

	if ( $page_id != awpcp_get_page_id_by_ref( 'show-ads-page-name' ) ) {
		return false;
	}

	$ad_id = intval( awpcp_request_param( 'id', '' ) );
	$ad_id = empty( $ad_id ) ? intval( get_query_var( 'id' ) ) : $ad_id;

	if ( empty( $ad_id ) ) {
		$url = get_permalink( $page_id );
	} else {
		$url = url_showad( $ad_id );
	}

	return $url;
}

/**
 * Set canonical URL to the Ad URL when in viewing on of AWPCP Ads.
 *
 * @since unknown
 * @since 3.2.1	logic moved to awpcp_rel_canonical_url()
 * @SuppressWarnings(PHPMD)
 */
function awpcp_rel_canonical() {
	$url = awpcp_rel_canonical_url();

	if ( $url ) {
		echo "<link rel='canonical' href='$url' />\n";
	} else {
		rel_canonical();
	}
}


/**
 * Overwrittes WP canonicalisation to ensure our rewrite rules
 * work, even when the main AWPCP page is also the front page or
 * when the requested page slug is 'awpcp'.
 *
 * Required for the View Categories and Classifieds RSS rules to work
 * when AWPCP main page is also the front page.
 *
 * http://wordpress.stackexchange.com/questions/51530/rewrite-rules-problem-when-rule-includes-homepage-slug
 *
 * @SuppressWarnings(PHPMD)
 */
function awpcp_redirect_canonical($redirect_url, $requested_url) {
	global $wp_query;

    $awpcp_rewrite = false;
	$ids = awpcp_get_page_ids_by_ref(awpcp_pages_with_rewrite_rules());

	// do not redirect requests to AWPCP pages with rewrite rules
	if (is_page() && in_array(awpcp_request_param('page_id', 0), $ids)) {
        $awpcp_rewrite = true;

	// do not redirect requests to the front page, if any of the AWPCP pages
	// with rewrite rules is the front page
	} else if (is_page() && !is_feed() && isset($wp_query->queried_object) &&
			  'page' == get_option('show_on_front') && in_array($wp_query->queried_object->ID, $ids) &&
			   $wp_query->queried_object->ID == get_option('page_on_front'))
	{
        $awpcp_rewrite = true;
	}

    if ( $awpcp_rewrite ) {
        // Fix for #943.
        $requested_host = parse_url( $requested_url, PHP_URL_HOST );
        $redirect_host = parse_url( $redirect_url, PHP_URL_HOST );

        if ( $requested_host != $redirect_host ) {
            if ( strtolower( $redirect_host ) == ( 'www.' . $requested_host ) ) {
                return str_replace( $requested_host, 'www.' . $requested_host, $requested_url );
            } elseif ( strtolower( $requested_host ) == ( 'www.' . $redirect_host ) ) {
                return str_replace( 'www.', '', $requested_url );
            }
        }

        return $requested_url;
    }

	// $id = awpcp_get_page_id_by_ref('main-page-name');

	// // do not redirect direct requests to AWPCP main page
	// if (is_page() && !empty($_GET['page_id']) && $id == $_GET['page_id']) {
	// 	$redirect_url = $requested_url;

	// // do not redirect request to the front page, if AWPCP main page is
	// // the front page
	// } else if (is_page() && !is_feed() && isset($wp_query->queried_object) &&
	// 		  'page' == get_option('show_on_front') && $id == $wp_query->queried_object->ID &&
	// 		   $wp_query->queried_object->ID == get_option('page_on_front'))
	// {
	// 	$redirect_url = $requested_url;
	// }

	return $redirect_url;
}
add_filter('redirect_canonical', 'awpcp_redirect_canonical', 10, 2);
