/*global AWPCP, Backbone, _*/
AWPCP.define( 'awpcp/categories-list-view', [
    'jquery',
    'awpcp/category-item-view'
], function( $, CategoryItemView ) {
    var CategoriesListView = Backbone.View.extend( {

        tagName: 'div',
        className: 'awpcp-categories-selector-categories-list',

        initialize: function( options ) {
            this.title = options.title;
            this.helper = options.helper;
        },

        render: function() {
            this.$el.html( this._getTemplate() );

            var categories = this.collection.map( function( model ) {
                return model.id;
            } );
            var $list = this.$( '.awpcp-categories-selector-categories-list-items' ).empty();

            if ( categories.length ) {
                return this._renderCategoriesList( $list, categories );
            } else {
                return this._renderEmptyList( $list );
            }
        },

        _getTemplate: function() {
            var template = '';

            template += '<div class="awpcp-categories-selector-categories-list-title">' + this.title + '</div>';
            template += '<ul class="awpcp-categories-selector-categories-list-items"></ul>';

            return template;
        },

        _renderCategoriesList: function( $list, categories ) {
            var visible = this.helper.getCategoriesAncestors( categories );
            var enabled = this._getEnabledCategories( categories );

            this._renderCategoryItems(
                $list,
                this.helper.getCategoryChildren( 'root' ),
                enabled,
                visible,
                0
            );

            return this;
        },

        _getEnabledCategories: function( categories ) {
            return categories;
        },

        _renderCategoryItems: function( $list, categories, enabled, visible, indentationLevel ) {
            var self = this;

            _.each( categories, function( category ) {
                if ( visible.indexOf( category.get( 'id' ) ) === -1 ) {
                    return;
                }

                $list.append( self._renderCategoryItem(
                    self.helper.getCategory( category.get( 'id' ) ),
                    enabled,
                    indentationLevel
                ) );

                self._renderCategoryItems(
                    $list,
                    self.helper.getCategoryChildren( category.get( 'id' ) ),
                    enabled,
                    visible,
                    indentationLevel + 1
                );
            } );
        },

        _renderCategoryItem: function( model, enabled, indentationLevel ) {
            var view = new CategoryItemView( {
                model: model,
                indentationLevel: indentationLevel,
                isEnabled: enabled.indexOf( model.get( 'id' ) ) !== -1,
                showPriceField: this.showPriceField
            } );

            return view.render().$el;
        },

        _renderEmptyList: function( $list ) {
            $list.append( $( '<li class="awpcp-categories-selector-category-item awpcp-categories-selector-empty-category-item">' + 'No categories to show.' + '</li>' ) );
            return this;
        }
    } );

    return CategoriesListView;
} );
