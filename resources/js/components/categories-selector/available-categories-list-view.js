/*global AWPCP*/
AWPCP.define( 'awpcp/available-categories-list-view', [
    'awpcp/categories-list-view'
], function( CategoriesListView ) {
    var AvailableCategoriesListView = CategoriesListView.extend({
        _getEnabledCategories: function( categories ) {
            return this.helper.getCategoriesThatCanBeSelectedTogether( categories );
        }
    });

    return AvailableCategoriesListView;
} );
