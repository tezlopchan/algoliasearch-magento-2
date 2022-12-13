var config = {
	map: {
		'*': {
			'autocomplete': 'Algolia_AlgoliaSearch/autocomplete',
			
			// Autocomplete templates
			'productsHtml': 'Algolia_AlgoliaSearch/internals/template/autocomplete/products',
			'pagesHtml': 'Algolia_AlgoliaSearch/internals/template/autocomplete/pages',
			'categoriesHtml': 'Algolia_AlgoliaSearch/internals/template/autocomplete/categories',
			'suggestionsHtml': 'Algolia_AlgoliaSearch/internals/template/autocomplete/suggestions',
			'additionalHtml': 'Algolia_AlgoliaSearch/internals/template/autocomplete/additional-section',
			// Recommend templates
			'recommendItemsElement': 'Algolia_AlgoliaSearch/internals/template/recommend/products'
		}
	},
	paths: {
		'algoliaBundle': 'Algolia_AlgoliaSearch/internals/algoliaBundle.min',
		'algoliaAnalytics': 'Algolia_AlgoliaSearch/internals/search-insights',
		'recommend': 'Algolia_AlgoliaSearch/internals/recommend.min',
		'recommendJs': 'Algolia_AlgoliaSearch/internals/recommend-js.min',
		'rangeSlider': 'Algolia_AlgoliaSearch/navigation/range-slider-widget',
	},
	deps: [
		'autocomplete'
	],
	config: {
		mixins: {
			'Magento_Catalog/js/catalog-add-to-cart': {
				'Algolia_AlgoliaSearch/insights/add-to-cart-mixin': true
			},
		}
	}
};
