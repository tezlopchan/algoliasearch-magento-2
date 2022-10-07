define([], function () {
    return {
        getCategoriesHtml: function (item, components, html) {
            return html `<a class="algoliasearch-autocomplete-hit" href="${item.url}">
                    ${components.Highlight({ hit: item, attribute: 'path' })} (${item.product_count})
                    </a>`;
        }
    };
});
