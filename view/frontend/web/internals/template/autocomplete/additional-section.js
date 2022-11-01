define([], function () {
    return {
        getNoResultHtml: function ({html}) {
            return html`<p>No Results</p>`;
        },

        getHeaderHtml: function ({section}) {
            return section.label || section.name;
        },

        getItemHtml: function ({item, components, html, section}) {
            return html`<a class="aa-ItemLink" href="/catalogsearch/result/?q=${encodeURIComponent(item.query)}&${section.name}=${encodeURIComponent(item.value)}"
                'objectId'=${item.objectID} 'indexName'=${item.__autocomplete_indexName} 'queryId'=${item.__autocomplete_queryID}>
                ${components.Highlight({ hit: item, attribute: 'value' })}
            </a>`;

        },

        getFooterHtml: function () {
            return "";
        }
    };
});
