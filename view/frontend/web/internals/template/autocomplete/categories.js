define([], function () {
    return {
        getNoResultHtml: function ({html}) {
            return html`<p>No Results</p>`;
        },

        getHeaderHtml: function ({section}) {
            return section.name;
        },

        getItemHtml: function ({item, components, html}) {
            return html `<a class="algoliasearch-autocomplete-hit" href="${item.url}"
                data-objectId=${item.objectID} data-index=${item.__autocomplete_indexName} data-queryId=${item.__autocomplete_queryID}>
                ${components.Highlight({ hit: item, attribute: 'path' })} (${item.product_count})
            </a>`;
        },

        getFooterHtml: function () {
            return "";
        },
    };
});
