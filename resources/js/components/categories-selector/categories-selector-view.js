/*global AWPCP, Backbone, _*/
AWPCP.define( 'awpcp/categories-selector-view', [
    'jquery',
    'awpcp/categories-collection',
    'awpcp/categories-list-view',
    'awpcp/available-categories-list-view',
],
function( $, CategoriesCollection, CategoriesListView, AvailableCategoriesListView ) {
    var CategoriesSelectorView = Backbone.View.extend( {

        initialize: function( options ) {
            var self = this;

            this.helper = options.helper;
            this.fieldName = options.fieldName;

            this.listenTo( this.collection, 'change:selected', function() {
                self.updateSelectedCategories();
                self.render();
            } );
        },

        render: function() {
            var self = this;
            var $element = this.$el;
            var $lists = this.$( '.awpcp-categories-selector-categories-lists' ).empty();
            var selectedCategories = this.getSelectedCategories();
            var view;

            this.$( '.awpcp-categories-selector-hidden-values' ).remove();

            _.each( selectedCategories, function( category ) {
                $element.append( $( '<input class="awpcp-categories-selector-hidden-values" type="hidden" name="' + self.fieldName + '[]" value="' + category.id + '">' ) );
            } );

            view = new CategoriesListView( {
                collection: new CategoriesCollection( selectedCategories ),
                title: 'Selected Categories',
                helper: this.helper
            } );
            $lists.append( view.render().$el );

            view = new AvailableCategoriesListView( {
                collection: new CategoriesCollection( this.collection.filter( function( model ) {
                    return model.get( 'selected' ) ? false : true;
                } ) ),
                title: 'Available Categories',
                helper: this.helper
            } );
            $lists.append( view.render().$el );

            return this;
        },

        updateSelectedCategories: function() {
            var selectedCategories = this.getSelectedCategories().map(function( category ) {
                return category.get( 'id' );
            });

            this.publishSelectedCategories( selectedCategories );
            this.helper.updateSelectedCategories( selectedCategories );
        },

        getSelectedCategories: function() {
            return this.collection.filter( function( model ) {
                return model.get( 'selected' ) ? true : false;
            } );
        },

        publishSelectedCategories: function( categoriesIds ) {
            $.publish( '/category/updated', [ $( this.el ), categoriesIds ] );
        }
    } );

    return CategoriesSelectorView;
} );
