define([], function () {
    return {
        getNoResultHtml: function ({html}) {
            return html`<p>${algoliaConfig.translations.noResults}</p>`;
        },

        getHeaderHtml: function ({section}) {
            return section.label;
        },

        getItemHtml: function ({item, html}) {
            return html`<a class="aa-ItemLink" href="${algoliaConfig.resultPageUrl}?q=${encodeURIComponent(item.query)}"
                data-objectId=${item.objectID} data-index=${item.__autocomplete_indexName} data-queryId=${item.__autocomplete_queryID}>
                ${item.query}
            </a>`;
        },

        getFooterHtml: function () {
            return "";
        }
    };
});
