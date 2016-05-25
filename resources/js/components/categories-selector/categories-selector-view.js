/*global AWPCP, Backbone, _*/
AWPCP.define( 'awpcp/categories-selector-view', [
    'jquery',
    'awpcp/categories-collection',
    'awpcp/categories-list-view',
],
function( $, CategoriesCollection, CategoriesListView ) {
    var CategoriesSelectorView = Backbone.View.extend( {

        initialize: function( options ) {
            var self = this;

            this.helper = options.helper;

            this.listenTo( this.collection, 'change:selected', function() {
                var selected = self.getSelectedCategories().map(function( category, index ) {
                    return category.get( 'id' );
                });

                $.publish( '/category/updated', [ $( self.el ), selected ] );
                self.render();
            } );
        },

        render: function() {
            var $lists = this.$( '.awpcp-categories-selector-categories-lists' ).empty();
            var view;

            view = new CategoriesListView( {
                collection: new CategoriesCollection( this.getSelectedCategories() ),
                title: 'Selected Categories',
                helper: this.helper
            } );
            $lists.append( view.render().$el );

            view = new CategoriesListView( {
                collection: new CategoriesCollection( this.collection.filter( function( model ) {
                    return model.get( 'selected' ) ? false : true;
                } ) ),
                title: 'Available Categories',
                helper: this.helper
            } );
            $lists.append( view.render().$el );

            return this;
        },

        getSelectedCategories: function() {
            return this.collection.filter( function( model ) {
                return model.get( 'selected' ) ? true : false;
            } );
        }
    } );

    return CategoriesSelectorView;
} );
