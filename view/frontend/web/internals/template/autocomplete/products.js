define([], function () {
    return {

        getColorHtml: (item, components, html) => {
            if (item._highlightResult.color == undefined || item._highlightResult.color.value == "") return "";

            return html`<span class="color">color: ${components.Highlight({ hit: item, attribute: "color" })}</span>`;
        },

        getCategoriesHtml: (item, components, html) => {
            if (item.categories_without_path == undefined || item.categories_without_path.length == 0) return "";

            return html`<span>in ${components.Highlight({ hit: item, attribute: "categories_without_path",})}</span>`;
        },

        getItemHtml: function (item, components, html) {
            var origFormatedVar = algoliaConfig.origFormatedVar;
            var tierFormatedvar = algoliaConfig.tierFormatedVar;
            if (algoliaConfig.priceGroup == null) {
                return html`<a class="algoliasearch-autocomplete-hit" href="${item.__autocomplete_queryID != null ? item.urlForInsights : item.url}">
                    <div class="thumb"><img src="${item.thumbnail_url || ''}" alt="${item.name || ''}"/></div>
                    <div class="info">
                        ${components.Highlight({hit: item, attribute: 'name'})}
                        <div class="algoliasearch-autocomplete-category">
                            ${this.getColorHtml(item, components, html)}
                            ${this.getCategoriesHtml(item, components, html)} 
                        </div>
                        ${item['price'] !== undefined ? html `<div className="algoliasearch-autocomplete-price">
                            <span className="after_special ${origFormatedVar != null ? 'promotion' : ''}">
                                ${item['price'][algoliaConfig.currencyCode]['default_formated']}
                            </span>
                            ${item['price'][algoliaConfig.currencyCode]['default_original_formated'] != null ? html`
                                <span class="before_special">${item['price'][algoliaConfig.currencyCode]['default_original_formated']}</span>` : ''}
                        </div>` : ''}
                    </div>
                </a>`;
            } else {
                return html`<a class="algoliasearch-autocomplete-hit" href="${item.__autocomplete_queryID != null ? item.urlForInsights : item.url}">
                    <div class="thumb"><img src="${item.thumbnail_url || ''}" alt="${item.name || ''}"/></div>
                    <div class="info">
                        ${components.Highlight({hit: item, attribute: 'name'}) || ''}
                        <div class="algoliasearch-autocomplete-category">
                            ${color && color != '' ? html `color : ${components.Highlight({hit: item, attribute: 'color'})}` :
                    item.categories_without_path && item.categories_without_path.length != 0 ? html `in ${components.Highlight({hit: item, attribute: 'categories_without_path'})}` : ''}
                        </div>
                        ${item['price'] !== undefined ? html `<div className="algoliasearch-autocomplete-price">
                            <span className="after_special ${origFormatedVar != null ? 'promotion' : ''}">
                                ${item['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_formated']}
                            </span>
                            ${item['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_original_formated'] != null ? html`
                                <span class="before_special">${item['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_original_formated']}</span>` : ''}

                            ${item['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_tier_formated'] != null ? html`
                                <span class="tier_price">As low as ${item['price'][algoliaConfig.currencyCode][algoliaConfig.priceGroup + '_tier_formated']}</span>` : ''}
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
