/*global module: false*/
/*jslint indent: 2*/

module.exports = function(grunt) {
	var pluginName = 'another-wordpress-classifieds-plugin';
	grunt.awpcp.registerPluginTasks( {
		name: 'awpcp',
		slug: 'awpcp',
		folder: pluginName,
		path: pluginName + '/resources',
		concat: {
			files: {
				'<%= path.awpcp %>resources/js/admin/debug-admin-page.src.js': '<%= path.awpcp %>resources/js/admin/debug-admin-page.js',
				'<%= path.awpcp %>resources/js/admin/edit-post.src.js': '<%= path.awpcp %>resources/js/admin/edit-post.js',
				'<%= path.awpcp %>resources/js/frontend/submit-listing-page.src.js': [
					'<%= path.awpcp %>resources/js/frontend/actions-section-controller.js',
					'<%= path.awpcp %>resources/js/frontend/order-section-controller.js',
					'<%= path.awpcp %>resources/js/frontend/listing-dates-section-controller.js',
					'<%= path.awpcp %>resources/js/frontend/listing-fields-section-controller.js',
					'<%= path.awpcp %>resources/js/frontend/save-section-controller.js',
					'<%= path.awpcp %>resources/js/frontend/upload-media-section-controller.js',
					'<%= path.awpcp %>resources/js/frontend/submit-listing-data-store.js',
					'<%= path.awpcp %>resources/js/frontend/submit-listing-page.js',
				],
				'<%= path.awpcp %>resources/js/awpcp.src.js': [
					'<%= path.awpcp %>resources/js/legacy.js',
					'<%= path.awpcp %>resources/js/awpcp.js',
					'<%= path.awpcp %>resources/js/jquery.js',
					'<%= path.awpcp %>resources/js/knockout.js',
					'<%= path.awpcp %>resources/js/moment.js',
					'<%= path.awpcp %>resources/js/components/categories-selector/*.js',
					'<%= path.awpcp %>resources/js/components/category-dropdown/*.js',
					'<%= path.awpcp %>resources/js/components/credit-plans-list/*.js',
					'<%= path.awpcp %>resources/js/components/datepicker-field/*.js',
					'<%= path.awpcp %>resources/js/components/file-manager/*.js',
					'<%= path.awpcp %>resources/js/components/media-manager/*.js',
					'<%= path.awpcp %>resources/js/components/media-uploader/*.js',
					'<%= path.awpcp %>resources/js/components/media-center.js',
					'<%= path.awpcp %>resources/js/components/messages/*.js',
					'<%= path.awpcp %>resources/js/components/multiple-region-selector/multiple-region-selector-validator.js',
					'<%= path.awpcp %>resources/js/components/payment-terms-list/*.js',
					'<%= path.awpcp %>resources/js/components/thumbnails-generator/*.js',
					'<%= path.awpcp %>resources/js/components/user-information-updater/*.js',
					'<%= path.awpcp %>resources/js/components/user-selector/*.js',
					'<%= path.awpcp %>resources/js/components/restricted-length-field.js',
					'<%= path.awpcp %>resources/js/util/guid.js',
					'<%= path.awpcp %>resources/js/util/number-format.js',
					'<%= path.awpcp %>resources/js/asynchronous-tasks.js',
					'<%= path.awpcp %>resources/js/asynchronous-tasks-group.js',
					'<%= path.awpcp %>resources/js/asynchronous-task.js',
					'<%= path.awpcp %>resources/js/collapsible.js',
					'<%= path.awpcp %>resources/js/localization.js',
					'<%= path.awpcp %>resources/js/settings.js',
					'<%= path.awpcp %>resources/js/users-autocomplete.js',
					'<%= path.awpcp %>resources/js/users-dropdown.js',
					'<%= path.awpcp %>resources/js/jquery-userfield.js',
					'<%= path.awpcp %>resources/js/jquery-collapsible.js',
					'<%= path.awpcp %>resources/js/jquery-validate-methods.js',
					'<%= path.awpcp %>resources/js/main.js',
					'<%= path.awpcp %>resources/js/recaptcha.js'
				],
				'<%= path.awpcp %>resources/js/admin-pointers.src.js': [
					'<%= path.awpcp %>resources/js/components/pointers/pointers-manager.js',
					'<%= path.awpcp %>resources/js/admin/pointers.js',
					'<%= path.awpcp %>resources/js/admin/drip-autoresponder.js'
				],
				'<%= path.awpcp %>resources/js/awpcp-admin.src.js': [
					'<%= path.awpcp %>resources/js/components/settings-validator.js',
				],
				'<%= path.awpcp %>resources/js/admin/listings-table.src.js': '<%= path.awpcp %>resources/js/admin/listings-table.js',
				'<%= path.awpcp %>resources/js/jquery-usableform/jquery-usableform.src.js': '<%= path.awpcp %>resources/js/jquery-usableform/jquery-usableform.js',
				'<%= path.awpcp %>resources/js/knockout-progress/knockout-progress.src.js': [
					'<%= path.awpcp %>resources/js/knockout-progress/knockout-progress.js'
				],
				'<%= path.awpcp %>resources/vendors/breakpoints.js/breakpoints.src.js': [
					'<%= path.awpcp %>resources/vendors/breakpoints.js/breakpoints.js'
				],
			}
		},
		less: {
			files: {
				'<%= path.awpcp %>resources/css/awpcpstyle.css': '<%= path.awpcp %>resources/less/frontend.less',
				'<%= path.awpcp %>resources/css/awpcpstyle-ie-6.css': '<%= path.awpcp %>resources/less/frontend-ie6.less',
				'<%= path.awpcp %>resources/css/awpcpstyle-lte-ie-7.css': '<%= path.awpcp %>resources/less/frontend-lte-ie-7.less',
				'<%= path.awpcp %>resources/css/awpcp-admin.css': '<%= path.awpcp %>resources/less/admin.less',
				'<%= path.awpcp %>resources/css/awpcp-admin-menu.css': '<%= path.awpcp %>resources/less/admin-menu.less',
			}
		},
		i18n: {
			textDomain: pluginName
		}
	} );
};
