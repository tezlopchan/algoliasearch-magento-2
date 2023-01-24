<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Algolia\AlgoliaSearch\Block;

use Magento\Catalog\Model\Product;
use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class RecommendProductView extends Template
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
     * @param Context $context
     * @param Registry $registry
     * @param ConfigHelper $configHelper
     * @param array $data
     */
    public function __construct(
        Context      $context,
        Registry     $registry,
        ConfigHelper $configHelper,
        array        $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
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
