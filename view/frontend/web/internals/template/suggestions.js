define([], function () {
    return {
        getSuggestionsHtml: function (item, html) {
            return html`<a class="aa-ItemLink" href="/search?q=${item.query}">
                ${item.query}
            </a>`;
        },

        getPagesHeaderHtml: function (section) {
            return section.name;
        },

        getNoResultHtml: function () {
            return 'No Results';
        }
    };
});
