let algoliaAutocomplete;
let suggestionSection = false;
let algoliaFooter;
let productResult = [];
requirejs(['algoliaBundle'], function(algoliaBundle) {
    algoliaAutocomplete = algoliaBundle;
    algoliaBundle.$(function ($) {

        /** We have nothing to do here if autocomplete is disabled **/
        if (!algoliaConfig.autocomplete.enabled) {
            return;
        }

        /**
         * Set autocomplete templates
         * For templating is used Hogan library
         * Docs: http://twitter.github.io/hogan.js/
         **/
        algoliaConfig.autocomplete.templates = {
            suggestions: algoliaBundle.Hogan.compile($('#autocomplete_suggestions_template').html()),
            products: algoliaBundle.Hogan.compile($('#autocomplete_products_template').html()),
            categories: algoliaBundle.Hogan.compile($('#autocomplete_categories_template').html()),
            pages: algoliaBundle.Hogan.compile($('#autocomplete_pages_template').html()),
            additionalSection: algoliaBundle.Hogan.compile($('#autocomplete_extra_template').html())
        };

        /**
         * Initialise Algolia client
         * Docs: https://www.algolia.com/doc/api-client/getting-started/instantiate-client-index/
         **/
        var algolia_client = algoliaBundle.algoliasearch(algoliaConfig.applicationId, algoliaConfig.apiKey);
        algolia_client.addAlgoliaAgent('Magento2 integration (' + algoliaConfig.extensionVersion + ')');

        var searchClient = algoliaBundle.algoliasearch(algoliaConfig.applicationId, algoliaConfig.apiKey);

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

        /** Setup autocomplete data sources **/
        var sources = [],
            i = 0;
        $.each(algoliaConfig.autocomplete.sections, function (name, section) {
            var source = getAutocompleteSource(section, algolia_client, $, i);

            if (source) {
                sources.push(source);
            }

            /** Those sections have already specific placeholder, so do not use the default aa-dataset-{i} class **/
            if (section.name !== 'suggestions' && section.name !== 'products') {
                i++;
            }
        });

        /**
         * Setup the autocomplete search input
         * For autocomplete feature is used Algolia's autocomplete.js library
         * Docs: https://github.com/algolia/autocomplete.js
         **/
        $(algoliaConfig.autocomplete.selector).each(function (i) {
            var menu = $(this);
            var options = {
                hint: false,
                templates: {
                    dropdownMenu: '#menu-template'
                },
                dropdownMenuContainer: "#algolia-autocomplete-container",
                debug: algoliaConfig.autocomplete.isDebugEnabled
            };

            if (isMobile() === true) {
                // Set debug to true, to be able to remove keyboard and be able to scroll in autocomplete menu
                options.debug = true;
            }

            if (algoliaConfig.removeBranding === false) {
                algoliaFooter = '<div id="algoliaFooter" class="footer_algolia"><a href="https://www.algolia.com/?utm_source=magento&utm_medium=link&utm_campaign=magento_autocompletion_menu" title="Search by Algolia" target="_blank"><img src="' +algoliaConfig.urls.logo + '"  alt="Search by Algolia" /></a></div>';
            }

            sources = algolia.triggerHooks('beforeAutocompleteSources', sources, algolia_client, algoliaBundle);
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

            let querySuggestionsPlugin = "";
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
                                        return html`<a class="aa-ItemLink" href="/search?q=${item.query}">
                                            ${item.query}
                                        </a>`;
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
                                        params: {
                                            hitsPerPage: data.hitsPerPage,
                                            distinct: true,
                                            facets: ['categories.level0'],
                                            numericFilters: 'visibility_search=1',
                                            ruleContexts: ['magento_filters', ''],
                                            analyticsTags: 'autocomplete',
                                            clickAnalytics: true
                                        },
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
                                        params: {
                                            hitsPerPage: data.hitsPerPage,
                                            distinct: true,
                                            numericFilters: data.name === "categories" && algoliaConfig.showCatsNotIncludedInNavigation === false ? 'include_in_menu=1' : '',
                                        },
                                    },
                                ],
                            });
                        },
                        templates: data.templates,
                    })
                }
            });
            algoliaAutocomplete.autocomplete({
                container: '#algoliaAutocomplete',
                placeholder: 'Search for products, categories, ...',
                detachedMediaQuery: 'none',
                plugins: [querySuggestionsPlugin],
				onSubmit(data){
					if(data.state.query && data.state.query !== null && data.state.query !== ""){
						window.location.href = `/catalogsearch/result/?q=${data.state.query}`;
					}
				},
                getSources({query, setContext}) {
                    return autocompleteConfig;
                },
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
});

function getHitsUrlParameter(url, name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(url);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}
