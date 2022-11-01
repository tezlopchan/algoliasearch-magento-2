define([], function () {
    return {
        getNoResultHtml: function ({html}) {
            return html`<p>No Results</p>`;
        },

        getHeaderHtml: function ({section}) {
            return section.name;
        },

        getItemHtml: function ({item, html}) {
            return html`<a class="aa-ItemLink" href="/catalogsearch/result/?q=${encodeURIComponent(item.query)}"
                'objectId'=${item.objectID} 'indexName'=${item.__autocomplete_indexName} 'queryId'=${item.__autocomplete_queryID}>
                ${item.query}
            </a>`;
        },

        getFooterHtml: function () {
            return "";
        }
    };
});
