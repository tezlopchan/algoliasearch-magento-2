define([], function () {
    return {
        getNoResultHtml: function ({html}) {
            return html`<p>No Results</p>`;
        },

        getHeaderHtml: function ({section}) {
            return section.label || section.name;
        },

        getItemHtml: function ({item, components, html}) {
            return html`<a class="algoliasearch-autocomplete-hit" href="${item.url}"
                'objectId'=${item.objectID} 'indexName'=${item.__autocomplete_indexName} 'queryId'=${item.__autocomplete_queryID}>
                <div class="info-without-thumb">
                    ${components.Highlight({hit: item, attribute: 'name'})}
                    <div class="details">
                        ${components.Highlight({hit: item, attribute: 'content'})}
                    </div>
                </div>
                <div class="algolia-clearfix"></div>
            </a>`;
        },

        getFooterHtml: function () {
            return "";
        }
    };
});
