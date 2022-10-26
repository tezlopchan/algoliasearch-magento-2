define(
    ['algoliaBundle', 'pagesHtml', 'categoriesHtml', 'productsHtml', 'suggestionsHtml', 'additionalHtml', 'domReady!'], 
    function(algoliaBundle, pagesHtml, categoriesHtml, productsHtml, suggestionsHtml, additionalHtml) {

    const DEFAULT_HITS_PER_SECTION = 2;
    
    let suggestionSection = false;
    let algoliaFooter;

    /** We have nothing to do here if autocomplete is disabled **/
    if (!algoliaConfig.autocomplete.enabled) {
        return;
    }

    algoliaBundle.$(function ($) {
        /**
         * Initialise Algolia client
         * Docs: https://www.algolia.com/doc/api-client/getting-started/instantiate-client-index/
         **/
        const algolia_client = algoliaBundle.algoliasearch(algoliaConfig.applicationId, algoliaConfig.apiKey);
        algolia_client.addAlgoliaAgent('Magento2 integration (' + algoliaConfig.extensionVersion + ')');

        const searchClient = algoliaBundle.algoliasearch(algoliaConfig.applicationId, algoliaConfig.apiKey);

        // autocomplete code moved from common.js to autocomplete.js
        const transformAutocompleteHit = function (hit, price_key, helper) {
            if (Array.isArray(hit.categories))
                hit.categories = hit.categories.join(', ');

            if (hit._highlightResult.categories_without_path && Array.isArray(hit.categories_without_path)) {
                hit.categories_without_path = $.map(hit._highlightResult.categories_without_path, function (category) {
                    return category.value;
                });

                hit.categories_without_path = hit.categories_without_path.join(', ');
            }

            let matchedColors = [];

            // TODO: Adapt this migrated code from common.js - helper not utilized
            if (helper && algoliaConfig.useAdaptiveImage === true) {
                if (hit.images_data && helper.state.facetsRefinements.color) {
                    matchedColors = helper.state.facetsRefinements.color.slice(0); // slice to clone
                }

                if (hit.images_data && helper.state.disjunctiveFacetsRefinements.color) {
                    matchedColors = helper.state.disjunctiveFacetsRefinements.color.slice(0); // slice to clone
                }
            }

            if (Array.isArray(hit.color)) {
                let colors = [];

                $.each(hit._highlightResult.color, function (i, color) {
                    if (color.matchLevel === undefined || color.matchLevel === 'none') {
                        return;
                    }

                    colors.push(color.value);

                    if (algoliaConfig.useAdaptiveImage === true) {
                        const matchedColor = color.matchedWords.join(' ');
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
                    const priceExpiration = hit['price'][algoliaConfig.currencyCode]['special_to_date'];

                    if (algoliaConfig.now > priceExpiration + 1) {
                        hit['price'][algoliaConfig.currencyCode]['default_formated'] = hit['price'][algoliaConfig.currencyCode]['default_original_formated'];
                        hit['price'][algoliaConfig.currencyCode]['default_original_formated'] = false;
                    }
                }
            }

            // Add to cart parameters
            const action = algoliaConfig.instant.addToCartParams.action + 'product/' + hit.objectID + '/';

            const correctFKey = getCookie('form_key');

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
                    const insightsDataUrlString = $.param({
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

        const getAutocompleteSource = function (section, algolia_client, i) {
            let options = {
                hitsPerPage: section.hitsPerPage || DEFAULT_HITS_PER_SECTION,
                analyticsTags: 'autocomplete',
                clickAnalytics: true,
                distinct: true
            };

            let source;

            if (section.name === "products") {
                options.facets = ['categories.level0'];
                options.numericFilters = 'visibility_search=1';
                options.ruleContexts = ['magento_filters', '']; // Empty context to keep backward compatibility for already created rules in dashboard

                options = algolia.triggerHooks('beforeAutocompleteProductSourceOptions', options);

                source =  {
                    name: section.name,
                    hitsPerPage: section.hitsPerPage,
                    paramName:algolia_client.initIndex(algoliaConfig.indexName + "_" + section.name),
                    options,
                    templates: {
                        noResults({html}) {
                            return productsHtml.getNoResultHtml({html});
                        },
                        header({items, html}) { 
                            return productsHtml.getHeaderHtml({items, html})
                        },
                        item({ item, components, html }) {
                            if(suggestionSection){
                                $('.aa-Panel').addClass('productColumn2');
                                $('.aa-Panel').removeClass('productColumn1');
                            }else{
                                $('.aa-Panel').removeClass('productColumn2');
                                $('.aa-Panel').addClass('productColumn1');
                            }
                            if(algoliaFooter && algoliaFooter !== undefined && algoliaFooter !== null && $('#algoliaFooter').length === 0){
                                $('.aa-PanelLayout').append(algoliaFooter);
                            }
                            const _data = transformAutocompleteHit(item, algoliaConfig.priceKey);
                            return productsHtml.getItemHtml({ item: _data, components, html });
                        },
                        footer({items, html}) {
                            const resultDetails = {};
                            if (items.length) {
                                const firstItem = items[0];
                                resultDetails.allDepartmentsUrl = algoliaConfig.baseUrl + '/catalogsearch/result/?q=' + encodeURIComponent(firstItem.query);
                                resultDetails.nbHits = firstItem.nbHits;

                                if (algoliaConfig.facets.find(facet => facet.attribute === 'categories')) {                                    
                                    const allCategories = Object.keys(firstItem.allCategories).map(key => {
                                        const url = resultDetails.allDepartmentsUrl + '&categories=' + encodeURIComponent(key);
                                        return {
                                            name: key,
                                            value: firstItem.allCategories[key],
                                            url
                                        };
                                    });
                                    //reverse value sort apparently...
                                    allCategories.sort((a, b) => b.value - a.value);
                                    resultDetails.allCategories = allCategories.slice(0, 2); 
                                }
                            }
                            return productsHtml.getFooterHtml({ html, ...resultDetails });
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
                    options,
                    templates: {
                        noResults({html}) {
                            return categoriesHtml.getNoResultHtml({html});
                        },
                        header({html, items}) {
                            return categoriesHtml.getHeaderHtml({section, html, items});
                        },
                        item({ item, components, html }) {
                            return categoriesHtml.getItemHtml({item, components, html});
                        },
                        footer({html, items}) {
                            return categoriesHtml.getFooterHtml({section, html, items});
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
                    options,
                    templates: {
                        noResults({html}) {
                            return pagesHtml.getNoResultHtml({html});
                        },
                        header({html, items}) {
                            return pagesHtml.getHeaderHtml({section, html, items});
                        },
                        item({item, components, html}) {
                            return pagesHtml.getItemHtml({item, components, html});
                        },
                        footer({html, items}) {
                            return pagesHtml.getFooterHtml({section, html, items});
                        }
                    }
                };
            }
            else if (section.name === "suggestions")
            {
                const suggestions_index = algolia_client.initIndex(algoliaConfig.indexName + "_suggestions");
                const products_index = algolia_client.initIndex(algoliaConfig.indexName + "_products"); // unused variable? 

                source = {
                    displayKey: 'query',
                    name: section.name,
                    hitsPerPage: section.hitsPerPage,
                    paramName: suggestions_index,
                    options,
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
                    options,
                    templates: {
                        noResults({html}) {
                            return additionalHtml.getNoResultHtml({html});
                        },
                        header({html, items}) {
                            return additionalHtml.getHeaderHtml({section, html, items});
                        },
                        item({ item, components, html }) {
                            return additionalHtml.getItemHtml({item, components, html, section});
                        },
                        footer({html, items}) {
                            return additionalHtml.getFooterHtml({section, html, items});
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
        $(algoliaConfig.autocomplete.selector).each(function () {
            let querySuggestionsPlugin = "";
            let sources = [];
            let autocompleteConfig = [];
            let options = {
                container: '#algoliaAutocomplete',
                placeholder: 'Search for products, categories, ...',
                debug: algoliaConfig.autocomplete.isDebugEnabled,
                detachedMediaQuery: 'none',
                onSubmit(data){
                    if(data.state.query && data.state.query !== null && data.state.query !== ""){
                        window.location.href = `/catalogsearch/result/?q=${data.state.query}`;
                    }
                },
                getSources() {
                    return autocompleteConfig;
                },
            };
            
            if (isMobile() === true) {
                // Set debug to true, to be able to remove keyboard and be able to scroll in autocomplete menu
                options.debug = true;
            }

            if (algoliaConfig.removeBranding === false) {
                algoliaFooter = `<div id="algoliaFooter" class="footer_algolia"><span class="algolia-search-by-label">${algoliaConfig.translations.searchBy}</span><a href="https://www.algolia.com/?utm_source=magento&utm_medium=link&utm_campaign=magento_autocompletion_menu" title="${algoliaConfig.translations.searchBy} Algolia" target="_blank"><img src="${algoliaConfig.urls.logo}" alt="${algoliaConfig.translations.searchBy} Algolia" /></a></div>`;
            }

            sources = algolia.triggerHooks('beforeAutocompleteSources', sources, algolia_client, algoliaBundle);
            options = algolia.triggerHooks('beforeAutocompleteOptions', options);

            // Keep for backward compatibility
            if (typeof algoliaHookBeforeAutocompleteStart === 'function') {
                console.warn('Deprecated! You are using an old API for Algolia\'s front end hooks. ' +
                    'Please, replace your hook method with new hook API. ' +
                    'More information you can find on https://www.algolia.com/doc/integration/magento-2/customize/custom-front-end-events/');

                const hookResult = algoliaHookBeforeAutocompleteStart(sources, options, algolia_client);

                sources = hookResult.shift();
                options = hookResult.shift();
            }

            /** Setup autocomplete data sources **/
            let i = 0;
            $.each(algoliaConfig.autocomplete.sections, function (...[, section]) {
                const source = getAutocompleteSource(section, algolia_client, i);

                if (source) {
                    sources.push(source);
                }

                /** TODO: Review this block - appears to only apply to Autocomplete v0 with Hogan templates which is now unsupported
                 * e.g. view/frontend/templates/autocomplete/menu.phtml **/
                /** Those sections have already specific placeholder,
                 * so do not use the default aa-dataset-{i} class to specify the placeholder **/
                if (section.name !== 'suggestions' && section.name !== 'products') {
                    i++;
                }
            });

            sources.forEach(function(data){
                if(data.name === "suggestions"){
                    suggestionSection = true;
                    querySuggestionsPlugin = algoliaBundle.createQuerySuggestionsPlugin.createQuerySuggestionsPlugin({
                        searchClient,
                        indexName: data.paramName.indexName,
                        transformSource({ source }) {
                            return {
                                ...source,
                                getItemUrl({ item }) {
                                    return `/search?q=${item.query}`;
                                },
                                templates: {
                                    noResults({html}) {
                                        return suggestionsHtml.getNoResultHtml({html});
                                    },
                                    header({html, items}) {
                                        return suggestionsHtml.getHeaderHtml({section: data, html, items});
                                    },
                                    item({item, html}) {
                                        return suggestionsHtml.getItemHtml({item, html})
                                    },
                                    footer({html, items}) {
                                        return suggestionsHtml.getFooterHtml({section: data, html, items})
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
                                // Stash additional data at item level
                                transformResponse({ results, hits }) {
                                    const resDetail = results[0];

                                    return hits.map(res => { 
                                        return res.map(hit => {
                                            return { 
                                                ...hit, 
                                                nbHits: resDetail.nbHits,
                                                allCategories: resDetail.facets['categories.level0'],
                                                query: resDetail.query
                                            }
                                        })
                                    });
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
                                // Stash additional data at item level
                                transformResponse({ results, hits }) {
                                    const resDetail = results[0];

                                    return hits.map(res => { 
                                        return res.map(hit => {
                                            return { 
                                                ...hit, 
                                                query: resDetail.query 
                                            }
                                        })
                                    });
                                },
                                
                            });
                        },
                        templates: data.templates,
                    })
                }
            });

            options.plugins = [querySuggestionsPlugin];
            /** Bind autocomplete feature to the input */
            let algoliaAutocompleteInstance = algoliaBundle.autocomplete(options);
            algoliaAutocompleteInstance = algolia.triggerHooks('afterAutocompleteStart', algoliaAutocompleteInstance);

            //Autocomplete insight click conversion
            if (algoliaConfig.ccAnalytics.enabled
                && algoliaConfig.ccAnalytics.conversionAnalyticsMode !== 'disabled') {
                    $(document).on('click', '.algoliasearch-autocomplete-hit', function(){
                        const $this = $(this);
                        if ($this.data('clicked')) return;

                        let itemUrl = $this.attr('href');
                        let eventData = algoliaInsights.buildEventData(
                            'Clicked', getHitsUrlParameter(itemUrl, 'objectID'), getHitsUrlParameter(itemUrl, 'indexName'), 1, getHitsUrlParameter(itemUrl, 'queryID')
                        );
                        algoliaInsights.trackClick(eventData);
                        $this.attr('data-clicked', true);
                    });
            }
        });
    });

    function getHitsUrlParameter(url, name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        const regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(url);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }
});
