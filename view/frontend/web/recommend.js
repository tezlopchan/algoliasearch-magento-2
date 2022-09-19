requirejs([
    'jquery',
    'recommend',
    'recommendJs',
    'algoliaBundle',
    'mage/translate'
], function ($, recommend, recommendJs, algoliaBundle, $tr) {
    this.config = algoliaConfig;
    this.defaultIndexName = algoliaConfig.indexName + '_products';
    const appId = this.config.applicationId;
    const apiKey = this.config.apiKey;
    const recommendClient = recommend(appId, apiKey);
    const indexName = this.defaultIndexName;
    if ($('body').hasClass('catalog-product-view') || $('body').hasClass('checkout-cart-index')) {
            // --- Add the current product objectID here ---
            const currentObjectID = objectId;
            if ((this.config.recommend.enabledFBT && $('body').hasClass('catalog-product-view')) || (this.config.recommend.enabledFBTInCart && $('body').hasClass('checkout-cart-index'))) {
                recommendJs.frequentlyBoughtTogether({
                    container: '#frequentlyBoughtTogether',
                    recommendClient,
                    indexName,
                    objectIDs: currentObjectID,
                    maxRecommendations: this.config.recommend.limitFBTProducts,
                    itemComponent({item, createElement, Fragment}) {
                        if (config.recommend.isAddToCartEnabledInFBT) {
                            return renderRecommendDataWithAddToCart(item, createElement);
                        }else{
                            return renderRecommendData(item, createElement);
                        }
                    },
                });
            }
            if ((this.config.recommend.enabledRelated && $('body').hasClass('catalog-product-view')) || (this.config.recommend.enabledRelatedInCart && $('body').hasClass('checkout-cart-index')))  {
                recommendJs.relatedProducts({
                    container: '#relatedProducts',
                    recommendClient,
                    indexName,
                    objectIDs: currentObjectID,
                    maxRecommendations: this.config.recommend.limitRelatedProducts,
                    itemComponent({item, createElement, Fragment}) {
                        if (config.recommend.isAddToCartEnabledInRelatedProduct) {
                            return renderRecommendDataWithAddToCart(item, createElement);;
                        }else{
                            return renderRecommendData(item, createElement)
                        }
                    },
                });
            }
        }

    if (this.config.recommend.enabledTrendItems && ((this.config.recommend.isTrendItemsEnabledInPDP && $('body').hasClass('catalog-product-view')) || (this.config.recommend.isTrendItemsEnabledInCartPage && $('body').hasClass('checkout-cart-index'))) ) {
        recommendJs.trendingItems({
            container: '#trendItems',
            facetName: this.config.recommend.trendItemFacetName ? this.config.recommend.trendItemFacetName : '',
            facetValue: this.config.recommend.trendItemFacetValue ? this.config.recommend.trendItemFacetValue : '',
            recommendClient,
            indexName,
            maxRecommendations: this.config.recommend.limitTrendingItems,
            itemComponent({item, createElement, Fragment}) {
                if (config.recommend.isAddToCartEnabledInTrendsItem) {
                    return renderRecommendDataWithAddToCart(item, createElement);;
                }else{
                    return renderRecommendData(item, createElement)
                }
            },
        });
    }else if(this.config.recommend.enabledTrendItems && typeof recommendTrendContainer !== "undefined"){
        let containerValue = "#"+recommendTrendContainer;
        recommendJs.trendingItems({
            container: containerValue,
            facetName: facetName ? facetName : '',
            facetValue: facetValue ? facetValue : '',
            recommendClient,
            indexName,
            maxRecommendations: numOfTrendsItem ? parseInt(numOfTrendsItem) : this.config.recommend.limitTrendingItems,
            itemComponent({item, createElement, Fragment}) {
                if (config.recommend.isAddToCartEnabledInTrendsItem) {
                    return renderRecommendDataWithAddToCart(item, createElement);;
                }else{
                    return renderRecommendData(item, createElement)
                }
            },
        });
    }
    function renderRecommendData(item, createElement){
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
    }
    function renderRecommendDataWithAddToCart(item, createElement){
        let correctFKey = getCookie('form_key');
        let action = config.recommend.addToCartParams.action + 'product/' + item.objectID + '/';
        if(correctFKey != "" && config.recommend.addToCartParams.formKey != correctFKey) {
            config.recommend.addToCartParams.formKey = correctFKey;
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
                    createElement('p', {className: "product-name"}, item.name),
                    createElement('form', {className: 'addTocartForm', action: action, method: 'post'},
                        createElement('input', {type: 'hidden', name: 'form_key',value: config.recommend.addToCartParams.formKey}),
                        createElement('input', {type: 'hidden', name:'unec', value: AlgoliaBase64.mageEncode(action)}),
                        createElement('input', {type: 'hidden', name:'product', value: item.objectID}),
                        createElement('button', {type: 'submit', className: 'action tocart primary'}, $tr('Add To Cart'))
                    )
                )
            )
        );
    }
});
