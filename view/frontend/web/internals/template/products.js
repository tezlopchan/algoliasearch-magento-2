define([], function () {
    return {
        getProductsHtml: function (_data, components, html) {
            if (_data._highlightResult.color !== undefined) {
                color = _data._highlightResult.color.value;
            }
            if (algoliaConfig.priceGroup == null) {
                return html`<a class="algoliasearch-autocomplete-hit" href="${_data.__autocomplete_queryID != null ? _data.urlForInsights : _data.url}">
                    <div class="thumb"><img src="${_data.thumbnail_url || ''}" alt="${_data.name || ''}"/></div>
                    <div class="info">
                        ${components.Highlight({hit: _data, attribute: 'name'}) || ''}
                        <div class="algoliasearch-autocomplete-category">
                            ${color && color != '' ? html `color : ${components.Highlight({hit: _data, attribute: 'color'})}` :
                                _data.categories_without_path && _data.categories_without_path.length != 0 ? html `in ${components.Highlight({hit: _data, attribute: 'categories_without_path'})}` : ''}
                        </div>
                        <div class="algoliasearch-autocomplete-price">
                            <span class="after_special ${origFormatedVar != null ? 'promotion' : ''}">
                                ${_data['price'][algoliaConfig.currencyCode]['default_formated']}
                            </span>
                            ${_data['price'][algoliaConfig.currencyCode]['default_original_formated'] != null ? html`
                                <span class="before_special">${_data['price'][algoliaConfig.currencyCode]['default_original_formated']}</span>` : ''}
                        </div>
                    </div>
                </a>`;
            } else {
                return html`<a class="algoliasearch-autocomplete-hit" href="${_data.__autocomplete_queryID != null ? _data.urlForInsights : _data.url}">
                    <div class="thumb"><img src="${_data.thumbnail_url || ''}" alt="${_data.name || ''}"/></div>
                    <div class="info">
                        ${components.Highlight({hit: _data, attribute: 'name'}) || ''}
                        <div class="algoliasearch-autocomplete-category">
                            ${color && color != '' ? html `color : ${components.Highlight({hit: _data, attribute: 'color'})}` :
                                _data.categories_without_path && _data.categories_without_path.length != 0 ? html `in ${components.Highlight({hit: _data, attribute: 'categories_without_path'})}` : ''}
                        </div>
                        <div class="algoliasearch-autocomplete-price">
                            <span class="after_special ${origFormatedVar != null ? 'promotion' : ''}">
                                ${_data['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_formated']}
                            </span>
                            ${_data['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_original_formated'] != null ? html`
                                <span class="before_special">${_data['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_original_formated']}</span>` : ''}

                            ${_data['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_tier_formated'] != null ? html`
                                <span class="tier_price">As low as ${_data['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_tier_formated']}</span>` : ''}
                        </div>
                    </div>
                </a>`;
            }
        }
    };
});
