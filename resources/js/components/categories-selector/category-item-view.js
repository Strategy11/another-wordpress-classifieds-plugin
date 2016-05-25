/*global AWPCP, Backbone, _*/
AWPCP.define( 'awpcp/category-item-view', [], function() {
    var CategoryItemView = Backbone.View.extend( {
        tagName: 'li',
        className: 'awpcp-categories-selector-category-item',

        events: {
            'click .awpcp-categories-selctor-category-action': 'sendButtonAction'
        },

        initialize: function( options  ) {
            this.template = this._getItemTemplate();

            this.indentationLevel = options.indentationLevel;
            this.isEnabled = options.isEnabled;

            if ( ! this.isEnabled ) {
                this.$el.addClass( 'awpcp-categories-selector-category-item-disabled' );
            }
        },

        render: function() {
            this.$el.html( this.template( {
                category: {
                    name: '&mdash;&nbsp;'.repeat( this.indentationLevel ) + this.model.get( 'name' ),
                },
                action: this.model.get( 'selected' ) ? 'Remove Category' : 'Add Category',
                showPriceField: this.showPriceField
            } ) );

            return this;
        },

        _getItemTemplate: function() {
            var template = '';

            template += '<span class="awpcp-categories-selector-category-item-name">';
            template +=     '<%= category.name %>';
            template += '</span>';
            template += '<span class="awpcp-align-text-right">';

            if ( this.showPriceField ) {
                template += '<label class="awpcp-categories-selector-category-item-price-field">';
                template +=     '<span>$</span>';
                template +=     '<input />';
                template += '</label>';
            }

            template +=     '<a class="awpcp-categories-selctor-category-action" href="#" ><%= action %></a>';
            template += '</span>';

            return _.template( template );
        },

        sendButtonAction: function( event ) {
            event.preventDefault();
            this.model.set( { selected: this.model.get('selected' ) ? false : true } );
        }
    } );

    return CategoryItemView;
} );
