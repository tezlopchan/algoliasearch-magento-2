var config = {
	paths: {
		'algoliaBundle': 'Algolia_AlgoliaSearch/internals/algoliaBundle.min',
		'algoliaAnalytics': 'Algolia_AlgoliaSearch/internals/search-insights',
        'recommend': 'Algolia_AlgoliaSearch/internals/recommend.min',
        'recommendJs': 'Algolia_AlgoliaSearch/internals/recommend-js.min',
		'rangeSlider': 'Algolia_AlgoliaSearch/navigation/range-slider-widget',
        'pagesHtml': 'Algolia_AlgoliaSearch/internals/template/pages',
        'categoriesHtml': 'Algolia_AlgoliaSearch/internals/template/categories',
        'productsHtml': 'Algolia_AlgoliaSearch/internals/template/products',
        'suggestionsHtml': 'Algolia_AlgoliaSearch/internals/template/suggestions',
        'additionalHtml': 'Algolia_AlgoliaSearch/internals/template/additional-section'
	},
	config: {
		mixins: {
			'Magento_Catalog/js/catalog-add-to-cart': {
				'Algolia_AlgoliaSearch/insights/add-to-cart-mixin': true
			},
		}
	}
};
