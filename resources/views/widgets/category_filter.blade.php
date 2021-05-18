<catalog-column-filter :config='@json($config)' :category-id='catalogFilter.category_id'
                       :selected-attributes="catalogFilter.attributes"
                       :price-slider="price_slider"
                       :active-prices="catalogFilter.price"
                       v-on:remove-attribute-param="removeAttributeParam"
                       v-on:set-attributes="setAttributes"
                       v-on:set-prices="setPrices"
></catalog-column-filter> 
