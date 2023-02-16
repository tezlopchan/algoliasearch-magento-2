<?php

namespace Algolia\AlgoliaSearch\ViewModel\Recommend;

use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template\Context;

class Cart implements ArgumentInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param ConfigHelper $configHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        ConfigHelper $configHelper,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->configHelper = $configHelper;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllCartItems()
    {
        $cartItems = [];
        $itemCollection = $this->checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($itemCollection as $item) {
            $cartItems[] = $item->getProductId();
        }
        return array_unique($cartItems);
    }

    /**
     * @return array
     */
    public function getAlgoliaRecommendConfiguration()
    {
        return [
            'enabledFBTInCart' => $this->configHelper->isRecommendFrequentlyBroughtTogetherEnabledOnCartPage(),
            'enabledRelatedInCart' => $this->configHelper->isRecommendRelatedProductsEnabledOnCartPage(),
            'isTrendItemsEnabledInCartPage' => $this->configHelper->isTrendItemsEnabledInShoppingCart()
        ];
    }
}
