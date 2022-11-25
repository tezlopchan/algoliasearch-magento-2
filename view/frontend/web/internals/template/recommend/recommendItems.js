define(['mage/translate'], function ($tr) {
    return {
        getRecommendItemsWithAddToCart: function (item, createElement) {
            let correctFKey = getCookie('form_key');
            let action = algoliaConfig.recommend.addToCartParams.action + 'product/' + item.objectID + '/';
            if(correctFKey != "" && algoliaConfig.recommend.addToCartParams.formKey != correctFKey) {
                config.recommend.addToCartParams.formKey = correctFKey;
            }
            this.config = algoliaConfig;
            this.defaultIndexName = algoliaConfig.indexName + '_products';
            return createElement(
                'div',
                null,
                createElement(
                    'div',
                    {className: "product-details"},
                    createElement(
                        'a',
                        {className: "recommend-item product-url", href: item.url, 'data-objectId': item.objectID, 'data-index': this.defaultIndexName},
                        createElement('img', {className: "product-img", src: item.image_url}, item.image_url),
                        createElement('p', {className: "product-name"}, item.name),
                        createElement('form', {className: 'addTocartForm', action: action, method: 'post', 'data-role':'tocart-form'},
                            createElement('input', {type: 'hidden', name: 'form_key',value: algoliaConfig.recommend.addToCartParams.formKey}),
                            createElement('input', {type: 'hidden', name:'unec', value: AlgoliaBase64.mageEncode(action)}),
                            createElement('input', {type: 'hidden', name:'product', value: item.objectID}),
                            createElement('button', {type: 'submit', className: 'action tocart primary'}, [
                                createElement('span', {}, $tr('Add To Cart'))
                            ])
                        )
                    )
                )
            );
        },

        getRecommendDataWithNoAddToCart: function (item, createElement) {
            this.config = algoliaConfig;
            this.defaultIndexName = algoliaConfig.indexName + '_products';
            return createElement(
                'div',
                null,
                createElement(
                    'div',
                    {className: "product-details"},
                    createElement(
                        'a',
                        {className: "recommend-item product-url", href: item.url, 'data-objectId': item.objectID, 'data-index': this.defaultIndexName},
                        createElement('img', {className: "product-img", src: item.image_url}, item.image_url),
                        createElement('p', {className: "product-name"}, item.name)
                    )
                )
            );
        },
    };
});
