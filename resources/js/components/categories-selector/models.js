/*global AWPCP, Backbone*/

AWPCP.define( 'awpcp/categories-collection', [], function() {
    var CategoriesCollection = Backbone.Collection.extend( {} );

    return CategoriesCollection;
} );

AWPCP.define( 'awpcp/category-item-model', [], function() {
    var CategoryItemModel = Backbone.Model.extend( {
        defaults: {
            id: 0,
            name: 'Default Category',
            price: 0,
            selected: false,
        }
    } );

    return CategoryItemModel;
} );
