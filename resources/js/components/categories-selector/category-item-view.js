/*global AWPCP, Backbone, _*/
AWPCP.define( 'awpcp/category-item-view', [ 'awpcp/settings' ], function( settings ) {
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
            if ( this.model.get( 'selected' ) ) {
                actionName = settings.l10n( 'multiple-categories-selector', 'remove-category-button' );
                actionClassName = 'dashicons dashicons-no';
            } else {
                actionName = settings.l10n( 'multiple-categories-selector', 'add-category-button' );
                actionClassName = 'dashicons dashicons-plus';
            }

            this.$el.html( this.template( {
                category: {
                    name: '&mdash;&nbsp;'.repeat( this.indentationLevel ) + this.model.get( 'name' ),
                },
                action: actionName,
                actionClassName: actionClassName,
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

            template +=     '<a class="awpcp-categories-selctor-category-action <%= actionClassName %>" href="#" title="<%= action %>"></a>';
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
