/*global AWPCP*/
AWPCP.define( 'awpcp/category-dropdown', [ 'jquery',  'awpcp/categories-selector-helper' ],
function( $, CategoriesSelectorHelper ) {
     const CategoriesDropdown = function( select, options ) {
         let self = this;
         this.$select = $( select );
         let identifier = this.$select.attr('target');
         this.options = $.extend(
             {},
             window[ 'categories_' + this.$select.attr( 'data-hash' ) ],
             options
         );
         this.options.helper = new CategoriesSelectorHelper(
             this.options.selectedCategoriesIds,
             this.options.categoriesHierarchy,
             this.options.paymentTerms
         );
        this.$container = $('.awpcp-multiple-category-dropdown-container');
        this.$hidden = $( '#awpcp-multiple-category-dropdown-' + identifier );
        let categoriesHierarchy = window['categories_'+ identifier]['categoriesHierarchy'];

         // add subcategory dropdown
        this.$container.on('change', '.awpcp-multiple-category-dropdown', function() {
            self.$select = $(this);
            let category = self.setCategory(this);
            let children = categoriesHierarchy[category[0]];
            $(this).nextAll('.awpcp-multiple-category-dropdown').remove();
            if (category[0] in categoriesHierarchy && children.length > 0 && self.$select.next('.awpcp-multiple-category-dropdown').length == 0) {
                let subDropdown = $('<select class="awpcp-multiple-category-dropdown"><option value="">Select a Sub-category (optional)</option></select>').insertAfter($(this));
                for (var i = 0; i < children.length; i = i + 1) {
                    subDropdown.append($('<option value="' + children[i].term_id + '">' + children[i].name + '</option>'));
                }
            }
        });

        this.$container.on( 'change.categoryDropdown', '.awpcp-multiple-category-dropdown', _.bind( this.onChange, this ));
    };

    $.extend( CategoriesDropdown.prototype, {
        onChange: function() {
            let categoriesIds = this.getSelectedCategoriesIds();

            this.options.helper.updateSelectedCategories( categoriesIds );

            if ( $.isFunction( this.options.onChange ) ) {
                this.options.onChange( this.getSelectedCategories() );
            }

            $.publish( '/categories/change', [ this.$select, this.getSelectedCategories() ] );
        },

        getSelectedCategories: function() {
            let category = this.$hidden.val();
            category = JSON.parse(category);
            return [{
                id: category[0],
                name: category[1]
            }];
        },

        getSelectedCategoriesIds: function() {
            let category = this.$hidden.val();
            category = JSON.parse(category);
            return [category[0]];
        },

        setCategory: function() {
            let category = [
                parseInt(this.$select.val(), 10) ? parseInt(this.$select.val(), 10) : null,
                $('option:selected', this.$select).text(),
            ];
            if (category[0] == null) {
                this.$select = this.$select.prev('.awpcp-multiple-category-dropdown');
                if (this.$select.length > 0) {
                    category = [
                        parseInt(this.$select.val(), 10),
                        $('option:selected', this.$select).text(),
                    ];
                }
            }
            this.$hidden.val(JSON.stringify(category));
            return category;
        },
    });

    return CategoriesDropdown;
} );
