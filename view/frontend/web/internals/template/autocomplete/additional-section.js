define([], function () {
    return {
        getItemHtml: function (item, components, html) {
            return html`${components.Highlight({ hit: item, attribute: 'value' })}`;
        },

        getHeaderHtml: function (section) {
            return section.name;
        },

        getNoResultHtml: function () {
            return 'No Results';
        }
    };
});
