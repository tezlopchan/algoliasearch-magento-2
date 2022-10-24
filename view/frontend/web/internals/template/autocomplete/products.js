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

        getOriginalPriceHtml: (item, html, priceGroup) => {
            if (item['price'][algoliaConfig.currencyCode][priceGroup + '_original_formated'] == null) return "";

            return html`<span class="before_special"> ${item['price'][algoliaConfig.currencyCode][priceGroup + '_original_formated']} </span>`;
        },

        getTierPriceHtml: (item, html, priceGroup) => {
            if (item['price'][algoliaConfig.currencyCode][priceGroup + '_tier_formated'] == null) return "";            

            return html`<span class="tier_price"> As low as <span class="tier_value">${item['price'][algoliaConfig.currencyCode][priceGroup + '_tier_formated']}</span></span>`;
        },

        getPricingHtml: function(item, html) { 
            if (item['price'] == undefined) return "";

            const priceGroup =  algoliaConfig.priceGroup || 'default'; 
            
            return html `<div className="algoliasearch-autocomplete-price">
                <span className="after_special ${algoliaConfig.origFormatedVar != null ? 'promotion' : ''}">
                    ${item['price'][algoliaConfig.currencyCode][priceGroup + '_formated']}
                </span>
                ${this.getOriginalPriceHtml(item, html, priceGroup)}

                ${this.getTierPriceHtml(item, html, priceGroup)}
            </div>`;
        },

        getItemHtml: function (item, components, html) {
            return html`<a class="algoliasearch-autocomplete-hit" href="${item.__autocomplete_queryID != null ? item.urlForInsights : item.url}">
                <div class="thumb"><img src="${item.thumbnail_url || ''}" alt="${item.name || ''}"/></div>
                <div class="info">
                    ${components.Highlight({hit: item, attribute: 'name'})}
                    <div class="algoliasearch-autocomplete-category">
                        ${this.getColorHtml(item, components, html)}
                        ${this.getCategoriesHtml(item, components, html)} 
                    </div>

                    ${this.getPricingHtml(item, html)}
                </div>
            </a>`;
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
