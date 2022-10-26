define([], function () {
    return {
        getNoResultHtml: function () {
            return 'No Results';
        },

        getHeaderHtml: function ({section}) {
            return section.name;
        },

        getItemHtml: function (item, html) {
            return html`<a class="aa-ItemLink" href="/search?q=${item.query}">
                ${item.query}
            </a>`;
        },

        getFooterHtml: function () {
            return "";
        }
    };
});
