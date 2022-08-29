requirejs([
    'jquery',
    'recommend',
    'recommendJs',
    'algoliaBundle'
], function ($, recommend, recommendJs, algoliaBundle) {
    this.config = algoliaConfig;
    this.defaultIndexName = algoliaConfig.indexName + '_products';
    const appId = this.config.applicationId;
    const apiKey = this.config.apiKey;
    const recommendClient = recommend(appId, apiKey);
    const indexName = this.defaultIndexName;
    if ($('body').hasClass('catalog-product-view')) {
            // --- Add the current product objectID here ---
            const currentObjectID = objectId;
            if (this.config.recommend.enabledFBT) {
                recommendJs.frequentlyBoughtTogether({
                    container: '#frequentlyBoughtTogether',
                    recommendClient,
                    indexName,
                    objectIDs: [currentObjectID],
                    maxRecommendations: this.config.recommend.limitFBTProducts,
                    itemComponent({item, createElement, Fragment}) {
                        var correctFKey = getCookie('form_key');
                        var action = config.instant.addToCartParams.action + 'product/' + item.objectID + '/';
                        if(correctFKey != "" && config.instant.addToCartParams.formKey != correctFKey) {
                            config.instant.addToCartParams.formKey = correctFKey;
                        }
                        return createElement(
                            'div',
                            null,
                            createElement(
                                'div',
                                {className: "product-details"},
                                createElement(
                                    'a',
                                    {className: "product-url", href: item.url},
                                    createElement('img', {className: "product-img", src: item.image_url}, item.image_url),
                                    createElement('p', {className: "product-name"}, item.name)
                                )
                            )
                        );
                    },
                });
            }
            if (this.config.recommend.enabledRelated) {
                recommendJs.relatedProducts({
                    container: '#relatedProducts',
                    recommendClient,
                    indexName,
                    objectIDs: [currentObjectID],
                    maxRecommendations: this.config.recommend.limitRelatedProducts,
                    itemComponent({item, createElement, Fragment}) {
                        var correctFKey = getCookie('form_key');
                        var action = config.instant.addToCartParams.action + 'product/' + item.objectID + '/';
                        if(correctFKey != "" && config.instant.addToCartParams.formKey != correctFKey) {
                            config.instant.addToCartParams.formKey = correctFKey;
                        }
                        return createElement(
                            'div',
                            null,
                            createElement(
                                'div',
                                {className: "product-details"},
                                createElement(
                                    'a',
                                    {className: "product-url", href: item.url},
                                    createElement('img', {className: "product-img", src: item.image_url}, item.image_url),
                                    createElement('p', {className: "product-name"}, item.name)
                                )
                            )
                        );
                    },
                });
            }
        }

    if (this.config.recommend.enabledTrendItems && ((this.config.recommend.isTrendItemsEnabledInPDP && $('body').hasClass('catalog-product-view')) || (this.config.recommend.isTrendItemsEnabledInCartPage && $('body').hasClass('checkout-cart-index'))) ) {
        if(this.config.recommend.trendItemsType == "trending_items_for_facets" && this.config.recommend.trendItemFacetName && this.config.recommend.trendItemFacetValue){
            recommendJs.trendingItems({
                container: '#trendItems',
                facetName: this.config.recommend.trendItemFacetName,
                facetValue: this.config.recommend.trendItemFacetValue,
                recommendClient,
                indexName,
                maxRecommendations: this.config.recommend.limitTrendingItems,
                itemComponent({item, createElement, Fragment}) {
                    return createElement(
                        'div',
                        null,
                        createElement(
                            'div',
                            {className: "product-details"},
                            createElement(
                                'a',
                                {className: "product-url", href: item.url},
                                createElement('img', {className: "product-img", src: item.image_url}, item.image_url),
                                createElement('p', {className: "product-name"}, item.name)
                            )
                        )
                    );
                },
            });
        } else {
            recommendJs.trendingItems({
                container: '#trendItems',
                recommendClient,
                indexName,
                maxRecommendations: this.config.recommend.limitTrendingItems,
                itemComponent({item, createElement, Fragment}) {
                    return createElement(
                        'div',
                        null,
                        createElement(
                            'div',
                            {className: "product-details"},
                            createElement(
                                'a',
                                {className: "product-url", href: item.url},
                                createElement('img', {className: "product-img", src: item.image_url}, item.image_url),
                                createElement('p', {className: "product-name"}, item.name)
                            )
                        )
                    );
                },
            });
        }
    }else if(this.config.recommend.enabledTrendItems && typeof recommendTrendContainer !== "undefined" && typeof facetName !== "undefined" && typeof facetValue !== "undefined"){
        let containerValue = "#"+recommendTrendContainer;
        recommendJs.trendingItems({
            container: containerValue,
            facetName: facetName,
            facetValue: facetValue,
            recommendClient,
            indexName,
            maxRecommendations: this.config.recommend.limitTrendingItems,
            itemComponent({item, createElement, Fragment}) {
                return createElement(
                    'div',
                    null,
                    createElement(
                        'div',
                        {className: "product-details"},
                        createElement(
                            'a',
                            {className: "product-url", href: item.url},
                            createElement('img', {className: "product-img", src: item.image_url}, item.image_url),
                            createElement('p', {className: "product-name"}, item.name)
                        )
                    )
                );
            },
        });
    }
});
