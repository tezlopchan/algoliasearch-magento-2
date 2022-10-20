define([], function () {
    return {
        getProductsHtml: function (_data, components, html) {
            var color = '';
            if (_data._highlightResult.color !== undefined) {
                color = _data._highlightResult.color.value;
            }
            var origFormatedVar = algoliaConfig.origFormatedVar;
            var tierFormatedvar = algoliaConfig.tierFormatedVar;
            if (algoliaConfig.priceGroup == null) {
                return html`<a class="algoliasearch-autocomplete-hit" href="${_data.__autocomplete_queryID != null ? _data.urlForInsights : _data.url}">
                    <div class="thumb"><img src="${_data.thumbnail_url || ''}" alt="${_data.name || ''}"/></div>
                    <div class="info">
                        ${components.Highlight({hit: _data, attribute: 'name'}) || ''}
                        <div class="algoliasearch-autocomplete-category">
                            ${color && color != '' ? html `color : ${components.Highlight({hit: _data, attribute: 'color'})}` :
                    _data.categories_without_path && _data.categories_without_path.length != 0 ? html `in ${components.Highlight({hit: _data, attribute: 'categories_without_path'})}` : ''}
                        </div>
                        ${_data['price'] !== undefined ? html `<div className="algoliasearch-autocomplete-price">
                            <span className="after_special ${origFormatedVar != null ? 'promotion' : ''}">
                                ${_data['price'][algoliaConfig.currencyCode]['default_formated']}
                            </span>
                            ${_data['price'][algoliaConfig.currencyCode]['default_original_formated'] != null ? html`
                                <span class="before_special">${_data['price'][algoliaConfig.currencyCode]['default_original_formated']}</span>` : ''}
                        </div>` : ''}
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
                        ${_data['price'] !== undefined ? html `<div className="algoliasearch-autocomplete-price">
                            <span className="after_special ${origFormatedVar != null ? 'promotion' : ''}">
                                ${_data['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_formated']}
                            </span>
                            ${_data['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_original_formated'] != null ? html`
                                <span class="before_special">${_data['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_original_formated']}</span>` : ''}

                            ${_data['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_tier_formated'] != null ? html`
                                <span class="tier_price">As low as ${_data['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_tier_formated']}</span>` : ''}
                        </div>` : ''}
                    </div>
                </a>`;
            }
        },

        getHeaderHtml: function (section) {
            return section.name;
        },

        getNoResultHtml: function () {
            return 'No Results';
        },

        getFooterHtml: function (html, orsTab, allUrl, productResult) {
            if(orsTab && orsTab.length > 0 && algoliaConfig.instant.enabled) {
                return html `<div id="autocomplete-products-footer">${algoliaConfig.translations.seeIn} <span><a href="${allUrl}">${algoliaConfig.translations.allDepartments}</a></span> (${productResult[0].nbHits}) ${algoliaConfig.translations.orIn}
                    ${orsTab.map((list, index) =>
                        index === 0 ? html` <span><a href="${list.url}">${list.name}</a></span>` : html`, <span><a href="${list.url}">${list.name}</a></span>`
                    )}
                </div>`;
            }else{
                return html `<div id="autocomplete-products-footer">${algoliaConfig.translations.seeIn} <span><a href="${allUrl}">${algoliaConfig.translations.allDepartments}</a></span> (${productResult[0].nbHits})</div>`;
            }
        }
    };
});
