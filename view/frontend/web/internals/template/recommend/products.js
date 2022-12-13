define(['mage/translate'], function ($tr) {
    return {
        getItemHtml: function (item, html, addTocart) {
            let correctFKey = getCookie('form_key');
            let action = algoliaConfig.recommend.addToCartParams.action + 'product/' + item.objectID + '/';
            if(correctFKey != "" && algoliaConfig.recommend.addToCartParams.formKey != correctFKey) {
                config.recommend.addToCartParams.formKey = correctFKey;
            }
            this.config = algoliaConfig;
            this.defaultIndexName = algoliaConfig.indexName + '_products';
            return  html`<div class="product-details">
                <a class="recommend-item product-url" href="${item.url}" data-objectid=${item.objectID}  data-index=${this.defaultIndexName}>
                    <img class="product-img" src="${item.image_url}" alt="${item.name}"/>
                    <p class="product-name">${item.name}</p>
                    ${addTocart && html`
                        <form class="addTocartForm" action="${action}" method="post" data-role="tocart-form">
                            <input type="hidden" name="form_key" value="${algoliaConfig.recommend.addToCartParams.formKey}" />
                            <input type="hidden" name="unec" value="${AlgoliaBase64.mageEncode(action)}"/>
                            <input type="hidden" name="product" value="${item.objectID}" />
                            <button type="submit" class="action tocart primary">
                                <span>Add To Cart</span>
                            </button>
                        </form>`
                    }
                </a>
            </div>`;
        },
    };
});
