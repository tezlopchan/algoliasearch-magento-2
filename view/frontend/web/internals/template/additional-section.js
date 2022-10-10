define([], function () {
    return {
        getAdditionalHtml: function (item, components, html) {
            return html`${components.Highlight({ hit: item, attribute: 'value' })}`;
        }
    };
});
