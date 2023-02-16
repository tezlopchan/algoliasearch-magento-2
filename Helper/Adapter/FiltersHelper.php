<?php

namespace Algolia\AlgoliaSearch\Helper\Adapter;

use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Algolia\AlgoliaSearch\Helper\Entity\Product\AttributeHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;

class FiltersHelper
{
    /** @var ConfigHelper */
    private $config;

    /** @var Registry */
    private $registry;

    /** @var CustomerSession */
    private $customerSession;

    /** @var AttributeHelper */
    private $attributeHelper;

    /** @var Http */
    private $request;

    /**
     * @param ConfigHelper $config
     * @param Registry $registry
     * @param CustomerSession $customerSession
     * @param AttributeHelper $attributeHelper
     * @param Http $request
     */
    public function __construct(
        ConfigHelper $config,
        Registry $registry,
        CustomerSession $customerSession,
        AttributeHelper $attributeHelper,
        Http $request
    ) {
        $this->config = $config;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->attributeHelper = $attributeHelper;
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the pagination filters from the url
     *
     * @return array
     */
    public function getPaginationFilters()
    {
        $paginationFilter = [];
        $page = !is_null($this->request->getParam('page')) ?
            (int) $this->request->getParam('page') - 1 :
            0;
        $paginationFilter['page'] = $page;

        return $paginationFilter;
    }

    /**
     * Get the category filters from the context
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getCategoryFilters($storeId)
    {
        $categoryFilter = [];
        $categoryId = null;
        $category = $this->registry->registry('current_category');

        if ($category) {
            $categoryId = $category->getEntityId();
        }

        if (!is_null($categoryId)) {
            $categoryFilter['facetFilters'][] = 'categoryIds:' . $categoryId;
        }

        return $categoryFilter;
    }

    /**
     * Get current landing page filters
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getLandingPageFilters($storeId)
    {
        $landingPageFilter = [];
        $landingPage = $this->registry->registry('current_landing_page');
        if ($landingPage) {
            $landingPageConfiguration = json_decode($landingPage->getConfiguration(), true);
            $landingPageFilter['facetFilters'] = $this->getFacetFilters($storeId, $landingPageConfiguration);
            $landingPageFilter = array_merge(
                $landingPageFilter,
                $this->getLandingPagePriceFilters($storeId, $landingPageConfiguration)
            );
        }

        return $landingPageFilter;
    }

    /**
     * Get current landing page query
     *
     * @return array
     */
    public function getLandingPageQuery()
    {
        $landingPage = $this->registry->registry('current_landing_page');
        $landingPageQuery = '';
        if ($landingPage) {
            $landingPageQuery = $landingPage->getQuery();
        }

        return $landingPageQuery;
    }

    /**
     * Get the facet filters from the url or landing page configuration
     *
     * @param int $storeId
     * @param string[] $parameters
     *
     * @return array
     */
    public function getFacetFilters($storeId, $parameters = null)
    {
        $facetFilters = [];
        // If the parameters variable is null, fetch them from the request
        if ($parameters === null) {
            $parameters = $this->request->getParams();
        }

        foreach ($this->config->getFacets($storeId) as $facet) {
            if (!isset($parameters[$facet['attribute']])) {
                continue;
            }

            $facetValues = is_array($parameters[$facet['attribute']]) ?
                $parameters[$facet['attribute']] :
                explode('~', $parameters[$facet['attribute']]);

            // Backward compatibility with native Magento filtering
            if (!$this->config->isInstantEnabled($storeId)) {
                foreach ($facetValues as $key => $facetValue) {
                    if (is_numeric($facetValue)) {
                        $facetValues[$key] = $this->getAttributeOptionLabelFromId($facet['attribute'], $facetValue);
                    }
                }
            }

            if ($facet['attribute'] == 'categories') {
                $level = '.level' . (count($facetValues) - 1);
                $facetFilters[] = $facet['attribute'] . $level . ':' . implode(' /// ', $facetValues);

                continue;
            }

            if ($facet['type'] === 'conjunctive') {
                foreach ($facetValues as $key => $facetValue) {
                    $facetFilters[] = $facet['attribute'] . ':' . $facetValue;
                }
            }

            if ($facet['type'] === 'disjunctive') {
                if (count($facetValues) > 1) {
                    foreach ($facetValues as $key => $facetValue) {
                        $facetValues[$key] = $facet['attribute'] . ':' . $facetValue;
                    }
                    $facetFilters[] = $facetValues;
                }
                if (count($facetValues) == 1) {
                    $facetFilters[] = $facet['attribute'] . ':' . $facetValues[0];
                }
            }
        }

        return $facetFilters;
    }

    /**
     * Get the disjunctive facets
     *
     * @param int $storeId
     * @param string[] $parameters
     *
     * @return array
     */
    public function getDisjunctiveFacets($storeId, $parameters = null)
    {
        $disjunctiveFacets = [];
        // If the parameters variable is null, fetch them from the request
        if ($parameters === null) {
            $parameters = $this->request->getParams();
        }

        foreach ($this->config->getFacets($storeId) as $facet) {
            if (isset($parameters[$facet['attribute']]) && $facet['type'] == 'disjunctive') {
                $disjunctiveFacets['disjunctiveFacets'][] = $facet['attribute'];
            }
        }

        return $disjunctiveFacets;
    }

    /**
     * Get the price filters from the url
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getPriceFilters($storeId)
    {
        $priceFilters = [];
        $paramPriceSliderData = $this->getParamPriceSlider($storeId);
        $priceSlider = $paramPriceSliderData['price_slider'];
        $paramPriceSlider = $paramPriceSliderData['param'];
        $prices = [];

        // Instantsearch price facet compatibility
        if (!is_null($this->request->getParam($paramPriceSlider))) {
            $pricesFilter = $this->request->getParam($paramPriceSlider);
            $prices = explode(':', $pricesFilter);
        }

        // Native Magento price facet compatibility
        if (!$this->config->isInstantEnabled($storeId) && !is_null($this->request->getParam('price'))) {
            $pricesFilter = $this->request->getParam('price');
            $prices = explode('-', $pricesFilter);
        }

        if (count($prices) == 2) {
            if ($prices[0] != '') {
                $priceFilters['numericFilters'][] = $priceSlider . '>=' . $prices[0];
            }
            if ($prices[1] != '') {
                $priceFilters['numericFilters'][] = $priceSlider . '<=' . $prices[1];
            }
        }

        return $priceFilters;
    }

    /**
     * Get the price filters from the landing Page configuration
     *
     * @param int $storeId
     * @param string[] $landingPageConfiguration
     *
     * @return array
     */
    public function getLandingPagePriceFilters($storeId, $landingPageConfiguration)
    {
        $priceFilters = [];
        $paramPriceSliderData = $this->getParamPriceSlider($storeId);
        $priceSlider = $paramPriceSliderData['price_slider'];
        $paramPriceSlider = $paramPriceSliderData['param'];

        if (isset($landingPageConfiguration[$priceSlider])
            && is_array($landingPageConfiguration[$priceSlider])) {
            $prices = $landingPageConfiguration[$priceSlider];
            foreach ($prices as $operator => $price) {
                $priceFilters['numericFilters'][] = $priceSlider . $operator . $price[0];
            }
        }

        return $priceFilters;
    }

    /**
     * Get the name of the param price slider (related to the currency and the use of customer groups)
     *
     * @param int $storeId
     *
     * @return array
     */
    private function getParamPriceSlider($storeId)
    {
        // Handle price filtering
        $currencyCode = $this->config->getCurrencyCode($storeId);
        $priceSlider = 'price.' . $currencyCode . '.default';

        if ($this->config->isCustomerGroupsEnabled($storeId)) {
            $groupId = $this->customerSession->isLoggedIn() ?
                $this->customerSession->getCustomer()->getGroupId() :
                0;
            $priceSlider = 'price.' . $currencyCode . '.group_' . $groupId;
        }

        return [
            'price_slider' => $priceSlider,
            'param' => str_replace('.', '_', $priceSlider),
        ];
    }

    /**
     * Get the raw query parameter (keeps the "__empty__" value)
     *
     * @return string
     */
    public function getRawQueryParameter()
    {
        return $this->request->getParam('q');
    }

    /**
     * Get the label of an attribute option from its id
     *
     * @param string $attribute
     * @param int $value
     *
     * @return string
     */
    private function getAttributeOptionLabelFromId($attribute, $value)
    {
        $attributeOptionLabel = '';
        $attrInfo = $this->attributeHelper->getAttributeInfo(
            \Magento\Catalog\Model\Product::ENTITY,
            $attribute
        );

        if ($attrInfo->getAttributeId()) {
            $option = $this->attributeHelper->getAttributeOptionById(
                $attrInfo->getAttributeId(),
                $value
            );

            if (is_array($option->getData())) {
                $attributeOptionLabel = $option['value'];
            }
        }

        return $attributeOptionLabel;
    }
}
