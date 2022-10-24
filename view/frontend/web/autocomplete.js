let algoliaAutocomplete;
let suggestionSection = false;
let algoliaFooter;
let productResult = [];
requirejs(
    ['jquery', 'algoliaBundle', 'pagesHtml', 'categoriesHtml', 'productsHtml', 'suggestionsHtml', 'additionalHtml', 'domReady!'], 
    function(jQuery, algoliaBundle, pagesHtml, categoriesHtml, productsHtml, suggestionsHtml, additionalHtml) 
    
    {
    algoliaAutocomplete = algoliaBundle;
    algoliaBundle.$(function ($) {

        /** We have nothing to do here if autocomplete is disabled **/
        if (!algoliaConfig.autocomplete.enabled) {
            return;
        }

        /**
         * Initialise Algolia client
         * Docs: https://www.algolia.com/doc/api-client/getting-started/instantiate-client-index/
         **/
        var algolia_client = algoliaBundle.algoliasearch(algoliaConfig.applicationId, algoliaConfig.apiKey);
        algolia_client.addAlgoliaAgent('Magento2 integration (' + algoliaConfig.extensionVersion + ')');

        var searchClient = algoliaBundle.algoliasearch(algoliaConfig.applicationId, algoliaConfig.apiKey);

        // autocomplete code moved from common.js to autocomplete.js
        window.transformAutocompleteHit = function (hit, price_key, $, helper) {
            if (Array.isArray(hit.categories))
                hit.categories = hit.categories.join(', ');

            if (hit._highlightResult.categories_without_path && Array.isArray(hit.categories_without_path)) {
                hit.categories_without_path = $.map(hit._highlightResult.categories_without_path, function (category) {
                    return category.value;
                });

                hit.categories_without_path = hit.categories_without_path.join(', ');
            }

            var matchedColors = [];

            if (helper && algoliaConfig.useAdaptiveImage === true) {
                if (hit.images_data && helper.state.facetsRefinements.color) {
                    matchedColors = helper.state.disjunctiveFacetsRefinements.color.slice(0); // slice to clone
                }

                if (hit.images_data && helper.state.disjunctiveFacetsRefinements.color) {
                    matchedColors = helper.state.disjunctiveFacetsRefinements.color.slice(0); // slice to clone
                }
            }

            if (Array.isArray(hit.color)) {
                var colors = [];

                $.each(hit._highlightResult.color, function (i, color) {
                    if (color.matchLevel === undefined || color.matchLevel === 'none') {
                        return;
                    }

                    colors.push(color.value);

                    if (algoliaConfig.useAdaptiveImage === true) {
                        var matchedColor = color.matchedWords.join(' ');
                        if (hit.images_data && color.fullyHighlighted && color.fullyHighlighted === true) {
                            matchedColors.push(matchedColor);
                        }
                    }
                });

                colors = colors.join(', ');
                hit._highlightResult.color = { value: colors };
            }
            else {
                if (hit._highlightResult.color && hit._highlightResult.color.matchLevel === 'none') {
                    hit._highlightResult.color = { value: '' };
                }
            }

            if (algoliaConfig.useAdaptiveImage === true) {
                $.each(matchedColors, function (i, color) {
                    color = color.toLowerCase();

                    if (hit.images_data[color]) {
                        hit.image_url = hit.images_data[color];
                        hit.thumbnail_url = hit.images_data[color];

                        return false;
                    }
                });
            }

            if (hit._highlightResult.color && hit._highlightResult.color.value && hit.categories_without_path) {
                if (hit.categories_without_path.indexOf('<em>') === -1 && hit._highlightResult.color.value.indexOf('<em>') !== -1) {
                    hit.categories_without_path = '';
                }
            }

            if (Array.isArray(hit._highlightResult.name))
                hit._highlightResult.name = hit._highlightResult.name[0];

            if (Array.isArray(hit.price)) {
                hit.price = hit.price[0];
                if (hit['price'] !== undefined && price_key !== '.' + algoliaConfig.currencyCode + '.default' && hit['price'][algoliaConfig.currencyCode][price_key.substr(1) + '_formated'] !== hit['price'][algoliaConfig.currencyCode]['default_formated']) {
                    hit['price'][algoliaConfig.currencyCode][price_key.substr(1) + '_original_formated'] = hit['price'][algoliaConfig.currencyCode]['default_formated'];
                }

                if (hit['price'][algoliaConfig.currencyCode]['default_original_formated']
                    && hit['price'][algoliaConfig.currencyCode]['special_to_date']) {
                    var priceExpiration = hit['price'][algoliaConfig.currencyCode]['special_to_date'];

                    if (algoliaConfig.now > priceExpiration + 1) {
                        hit['price'][algoliaConfig.currencyCode]['default_formated'] = hit['price'][algoliaConfig.currencyCode]['default_original_formated'];
                        hit['price'][algoliaConfig.currencyCode]['default_original_formated'] = false;
                    }
                }
            }

            // Add to cart parameters
            var action = algoliaConfig.instant.addToCartParams.action + 'product/' + hit.objectID + '/';

            var correctFKey = getCookie('form_key');

            if(correctFKey != "" && algoliaConfig.instant.addToCartParams.formKey != correctFKey) {
                algoliaConfig.instant.addToCartParams.formKey = correctFKey;
            }

            hit.addToCart = {
                'action': action,
                'uenc': AlgoliaBase64.mageEncode(action),
                'formKey': algoliaConfig.instant.addToCartParams.formKey
            };

            if (hit.__autocomplete_queryID) {

                hit.urlForInsights = hit.url;

                if (algoliaConfig.ccAnalytics.enabled
                    && algoliaConfig.ccAnalytics.conversionAnalyticsMode !== 'disabled') {
                    var insightsDataUrlString = $.param({
                        queryID: hit.__autocomplete_queryID,
                        objectID: hit.objectID,
                        indexName: hit.__autocomplete_indexName
                    });
                    if (hit.url.indexOf('?') > -1) {
                        hit.urlForInsights += insightsDataUrlString
                    } else {
                        hit.urlForInsights += '?' + insightsDataUrlString;
                    }
                }
            }

            return hit;
        };

        window.getAutocompleteSource = function (section, algolia_client, $, i) {
            if (section.hitsPerPage <= 0)
                return null;

            var options = {
                hitsPerPage: section.hitsPerPage,
                analyticsTags: 'autocomplete',
                clickAnalytics: true,
                distinct: true
            };

            var source;

            if (section.name === "products") {
                options.facets = ['categories.level0'];
                options.numericFilters = 'visibility_search=1';
                options.ruleContexts = ['magento_filters', '']; // Empty context to keep backward compatibility for already created rules in dashboard

                options = algolia.triggerHooks('beforeAutocompleteProductSourceOptions', options);

                source =  {
                    name: section.name,
                    hitsPerPage: section.hitsPerPage,
                    paramName:algolia_client.initIndex(algoliaConfig.indexName + "_" + section.name),
                    options:options,
                    templates: {
                        noResults() {
                            return productsHtml.getNoResultHtml();
                        },
                        item({ item, components, html }) {
                            if(suggestionSection){
                                algoliaAutocomplete.$('.aa-Panel').addClass('productColumn2');
                                algoliaAutocomplete.$('.aa-Panel').removeClass('productColumn1');
                            }else{
                                algoliaAutocomplete.$('.aa-Panel').removeClass('productColumn2');
                                algoliaAutocomplete.$('.aa-Panel').addClass('productColumn1');
                            }
                            if(algoliaFooter && algoliaFooter !== undefined && algoliaFooter !== null && algoliaAutocomplete.$('#algoliaFooter').length === 0){
                                algoliaAutocomplete.$('.aa-PanelLayout').append(algoliaFooter);
                            }
                            var _data = transformAutocompleteHit(item, algoliaConfig.priceKey, $);
                            return productsHtml.getItemHtml(_data, components, html);
                        },
                        footer({html}) {
                            var keys = [];
                            for (var i = 0; i<algoliaConfig.facets.length; i++) {
                                if (algoliaConfig.facets[i].attribute == "categories" && productResult[0]) {
                                    for (var key in productResult[0].facets['categories.level0']) {
                                        var url = algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + encodeURIComponent(productResult[0].query) + '#q=' + encodeURIComponent(productResult[0].query) + '&hFR[categories.level0][0]=' + encodeURIComponent(key) + '&idx=' + algoliaConfig.indexName + '_products';
                                        keys.push({
                                            key: key,
                                            value: productResult[0].facets['categories.level0'][key],
                                            url: url
                                        });
                                    }
                                }
                            }

                            keys.sort(function (a, b) {
                                return b.value - a.value;
                            });

                            var orsTab = [];

                            if (keys.length > 0) {
                                orsTab = [];
                                for (var i = 0; i < keys.length && i < 2; i++) {
                                    orsTab.push(
                                        {
                                            url:keys[i].url,
                                            name:keys[i].key
                                        }
                                    );
                                }
                            }

                            var allUrl = algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + encodeURIComponent(productResult[0].query);
                            return productsHtml.getFooterHtml(html, orsTab, allUrl, productResult)
                        }
                    }
                };
            }
            else if (section.name === "categories")
            {
                if (section.name === "categories" && algoliaConfig.showCatsNotIncludedInNavigation === false) {
                    options.numericFilters = 'include_in_menu=1';
                }
                source =  {
                    name: section.name || i,
                    hitsPerPage: section.hitsPerPage,
                    paramName:algolia_client.initIndex(algoliaConfig.indexName + "_" + section.name),
                    options:options,
                    templates: {
                        noResults() {
                            return categoriesHtml.getNoResultHtml();
                        },
                        header() {
                            return categoriesHtml.getHeaderHtml(section);
                        },
                        item({ item, components, html }) {
                            return categoriesHtml.getItemHtml(item, components, html);
                        }
                    }
                };
            }
            else if (section.name === "pages")
            {
                source =  {
                    name: section.name || i,
                    hitsPerPage: section.hitsPerPage,
                    paramName:algolia_client.initIndex(algoliaConfig.indexName + "_" + section.name),
                    options:options,
                    templates: {
                        noResults() {
                            return pagesHtml.getNoResultHtml();
                        },
                        header() {
                            return pagesHtml.getHeaderHtml(section);
                        },
                        item({ item, components, html }) {
                            return pagesHtml.getItemHtml(item, components, html);
                        }
                    }
                };
            }
            else if (section.name === "suggestions")
            {
                var suggestions_index = algolia_client.initIndex(algoliaConfig.indexName + "_suggestions");
                var products_index = algolia_client.initIndex(algoliaConfig.indexName + "_products");

                source = {
                    displayKey: 'query',
                    name: section.name,
                    hitsPerPage: section.hitsPerPage,
                    paramName: suggestions_index,
                    options:options,
                    templates: {
                        item({ item, html }) {
                            return html`<div>Suggestion List</div>`;
                        }
                    }
                };
            } else {
                /** If is not products, categories, pages or suggestions, it's additional section **/
                source = {
                    paramName: algolia_client.initIndex(algoliaConfig.indexName + "_section_" + section.name),
                    displayKey: 'value',
                    name: section.name || i,
                    hitsPerPage: section.hitsPerPage,
                    options:options,
                    templates: {
                        noResults() {
                            return additionalHtml.getNoResultHtml();
                        },
                        header() {
                            return additionalHtml.getHeaderHtml(section);
                        },
                        item({ item, components, html }) {
                            return additionalHtml.getItemHtml(item, components, html);
                        }
                    }
                };
            }

            return source;
        };
        /** Add products and categories that are required sections **/
        /** Add autocomplete menu sections **/
        if (algoliaConfig.autocomplete.nbOfProductsSuggestions > 0) {
            algoliaConfig.autocomplete.sections.unshift({ hitsPerPage: algoliaConfig.autocomplete.nbOfProductsSuggestions, label: algoliaConfig.translations.products, name: "products"});
        }

        if (algoliaConfig.autocomplete.nbOfCategoriesSuggestions > 0) {
            algoliaConfig.autocomplete.sections.unshift({ hitsPerPage: algoliaConfig.autocomplete.nbOfCategoriesSuggestions, label: algoliaConfig.translations.categories, name: "categories"});
        }

        if (algoliaConfig.autocomplete.nbOfQueriesSuggestions > 0) {
            algoliaConfig.autocomplete.sections.unshift({ hitsPerPage: algoliaConfig.autocomplete.nbOfQueriesSuggestions, label: '', name: "suggestions"});
        }

        /**
         * Setup the autocomplete search input
         * For autocomplete feature is used Algolia's autocomplete.js library
         * Docs: https://github.com/algolia/autocomplete.js
         **/
        $(algoliaConfig.autocomplete.selector).each(function (i) {
            let querySuggestionsPlugin = "";
            var options = {
                container: '#algoliaAutocomplete',
                placeholder: 'Search for products, categories, ...',
                debug: algoliaConfig.autocomplete.isDebugEnabled,
                detachedMediaQuery: 'none',
                onSubmit(data){
                    if(data.state.query && data.state.query !== null && data.state.query !== ""){
                        window.location.href = `/catalogsearch/result/?q=${data.state.query}`;
                    }
                },
                getSources({query, setContext}) {
                    return autocompleteConfig;
                },
            };

            if (isMobile() === true) {
                // Set debug to true, to be able to remove keyboard and be able to scroll in autocomplete menu
                options.debug = true;
            }

            if (algoliaConfig.removeBranding === false) {
                algoliaFooter = '<div id="algoliaFooter" class="footer_algolia"><a href="https://www.algolia.com/?utm_source=magento&utm_medium=link&utm_campaign=magento_autocompletion_menu" title="Search by Algolia" target="_blank"><img src="' +algoliaConfig.urls.logo + '"  alt="Search by Algolia" /></a></div>';
            }

            sources = algolia.triggerHooks('beforeAutocompleteSources', algoliaConfig.autocomplete.sections, algolia_client, algoliaBundle);
            options = algolia.triggerHooks('beforeAutocompleteOptions', options);

            // Keep for backward compatibility
            if (typeof algoliaHookBeforeAutocompleteStart === 'function') {
                console.warn('Deprecated! You are using an old API for Algolia\'s front end hooks. ' +
                    'Please, replace your hook method with new hook API. ' +
                    'More information you can find on https://www.algolia.com/doc/integration/magento-2/customize/custom-front-end-events/');

                var hookResult = algoliaHookBeforeAutocompleteStart(sources, options, algolia_client);

                sources = hookResult.shift();
                options = hookResult.shift();
            }

            /** Setup autocomplete data sources **/
            var sources = [],
                i = 0;
            $.each(algoliaConfig.autocomplete.sections, function (name, section) {
                var source = getAutocompleteSource(section, algolia_client, $, i);

                if (source) {
                    sources.push(source);
                }

                /** Those sections have already specific placeholder,
                 * so do not use the default aa-dataset-{i} class to specify the placeholder **/
                if (section.name !== 'suggestions' && section.name !== 'products') {
                    i++;
                }
            });

            let autocompleteConfig = [];
            sources.forEach(function(data){
                if(data.name === "suggestions"){
                    suggestionSection = true;
                    querySuggestionsPlugin = algoliaAutocomplete.createQuerySuggestionsPlugin.createQuerySuggestionsPlugin({
                        searchClient,
                        indexName: data.paramName.indexName,
                        transformSource({ source }) {
                            return {
                                ...source,
                                getItemUrl({ item }) {
                                    return `/search?q=${item.query}`;
                                },
                                templates: {
                                    noResults() {
                                        return 'No results.';
                                    },
                                    header() {
                                        return sources[0].name;
                                    },
                                    item(params) {
                                        const { item, html } = params;
                                        return suggestionsHtml.getItemHtml(item, html)
                                    },
                                },
                            };
                        },
                    });
                }else if(data.name === "products"){
                    autocompleteConfig.unshift({
                        sourceId: data.name,
                        getItems({ query }) {
                            return algoliaBundle.getAlgoliaResults({
                                searchClient,
                                queries: [
                                    {
                                        indexName: data.paramName.indexName,
                                        query,
                                        params: data.options,
                                    },
                                ],
                                transformResponse({ results, hits }) {
                                    productResult = results;
                                    return hits;
                                },
                            });
                        },
                        templates: data.templates,
                    })
                }else{
                    autocompleteConfig.push({
                        sourceId: data.name,
                        getItems({ query }) {
                            return algoliaBundle.getAlgoliaResults({
                                searchClient,
                                queries: [
                                    {
                                        indexName: data.paramName.indexName,
                                        query,
                                        params: data.options,
                                    },
                                ],
                            });
                        },
                        templates: data.templates,
                    })
                }
            });

            options.plugins = [querySuggestionsPlugin];
            /** Bind autocomplete feature to the input */
            var algoliaAutocompleteInstance = algoliaAutocomplete.autocomplete(options);
            algoliaAutocompleteInstance = algolia.triggerHooks('afterAutocompleteStart', algoliaAutocompleteInstance);

            //Autocomplete insight click conversion
            jQuery(document).on('click', '.algoliasearch-autocomplete-hit', function(){
                let itemUrl = jQuery(this).attr('href');
                let eventData = algoliaInsights.buildEventData(
                    'Clicked', getHitsUrlParameter(itemUrl, 'objectID'), getHitsUrlParameter(itemUrl, 'indexName'), 1, getHitsUrlParameter(itemUrl, 'queryID')
                );
                algoliaInsights.trackClick(eventData);
            });
           //Written code for autocomplete insight 
            jQuery(document).on('click', '.algoliasearch-autocomplete-hit', function(){
                let itemUrl = jQuery(this).attr('href');
                let eventData = algoliaInsights.buildEventData(
                    'Clicked', getHitsUrlParameter(itemUrl, 'objectID'), getHitsUrlParameter(itemUrl, 'indexName'), 1, getHitsUrlParameter(itemUrl, 'queryID')
                );
                algoliaInsights.trackClick(eventData);
            });
        });
    });

    function getHitsUrlParameter(url, name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(url);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }
});

function getHitsUrlParameter(url, name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(url);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}
