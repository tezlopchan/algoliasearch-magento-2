define([], function () {
    return {
        getSuggestionsHtml: function (item, html) {
            return html`<a class="aa-ItemLink" href="/search?q=${item.query}">
                ${item.query}
            </a>`;
        }
    };
});
