define([], function () {
    return {
        getNoResultHtml: function () {
            return 'No Results';
        },

        getHeaderHtml: function ({section}) {
            return section.name;
        },

        getItemHtml: function ({item, components, html}) {
            return html `<a class="algoliasearch-autocomplete-hit" href="${item.url}">
                ${components.Highlight({ hit: item, attribute: 'path' })} (${item.product_count})
            </a>`;
        },
        
        getFooterHtml: function () {
            return "";
        },
    };
});
