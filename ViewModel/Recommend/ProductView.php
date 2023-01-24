<?php

namespace Algolia\AlgoliaSearch\ViewModel\Recommend;

use Magento\Catalog\Model\Product;
use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ProductView implements ArgumentInterface
{
    /**
     * @var Product
     */
    protected $product = null;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @param Registry $registry
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Registry     $registry,
        ConfigHelper $configHelper
    ) {
        $this->coreRegistry = $registry;
        $this->configHelper = $configHelper;
    }
    /**
     * Returns a Product
     *
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->product) {
            $this->product = $this->coreRegistry->registry('product');
        }
        return $this->product;
    }

    /**
     * @return array
     */
    public function getAlgoliaRecommendConfiguration()
    {
        return [
            'enabledFBT' => $this->configHelper->isRecommendFrequentlyBroughtTogetherEnabled(),
            'enabledRelated' => $this->configHelper->isRecommendRelatedProductsEnabled(),
            'isTrendItemsEnabledInPDP' => $this->configHelper->isTrendItemsEnabledInPDP()
        ];
    }
}
