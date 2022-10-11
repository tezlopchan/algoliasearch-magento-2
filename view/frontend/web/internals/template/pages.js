define([], function () {
    return {
        getPagesHtml: function (item, components, html) {
            return html`<a class="algoliasearch-autocomplete-hit" href="${item.url}">
                <div class="info-without-thumb">
                    ${components.Highlight({hit: item, attribute: 'name'})}
                    <div class="details">
                        ${components.Highlight({hit: item, attribute: 'content'})}
                    </div>
                </div>
                <div class="algolia-clearfix"></div>
            </a>`;
        },

        getHeaderHtml: function (section) {
            return section.name;
        },

        getNoResultHtml: function () {
            return 'No Results';
        }
    };
});
