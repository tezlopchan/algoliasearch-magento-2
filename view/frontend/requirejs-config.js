var config = {
	paths: {
		'algoliaBundle': 'Algolia_AlgoliaSearch/internals/algoliaBundle.min',
		'algoliaAnalytics': 'Algolia_AlgoliaSearch/internals/search-insights',
        'recommend': 'Algolia_AlgoliaSearch/internals/recommend.min',
        'recommendJs': 'Algolia_AlgoliaSearch/internals/recommend-js.min',
		'rangeSlider': 'Algolia_AlgoliaSearch/navigation/range-slider-widget'
	},
	config: {
		mixins: {
			'Magento_Catalog/js/catalog-add-to-cart': {
				'Algolia_AlgoliaSearch/insights/add-to-cart-mixin': true
			},
		}
	}
};
