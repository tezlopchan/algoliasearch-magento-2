<?php

namespace Algolia\AlgoliaSearch\Observer\Insights;

use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Algolia\AlgoliaSearch\Helper\Data;
use Algolia\AlgoliaSearch\Helper\InsightsHelper;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;

class CheckoutCartProductAddAfter implements ObserverInterface
{

    /** @var Data */
    protected Data $dataHelper;

    /** @var InsightsHelper */
    protected InsightsHelper $insightsHelper;

    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    /** @var ConfigHelper  */
    protected ConfigHelper $configHelper;

    /**
     * @param Data $dataHelper
     * @param InsightsHelper $insightsHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $dataHelper,
        InsightsHelper $insightsHelper,
        LoggerInterface $logger
    ) {
        $this->dataHelper = $dataHelper;
        $this->insightsHelper = $insightsHelper;
        $this->logger = $logger;
        $this->configHelper = $this->insightsHelper->getConfigHelper();
    }

    /**
     * @param Observer $observer
     * ['quote_item' => $result, 'product' => $product]
     */
    public function execute(Observer $observer)
    {
        /** @var Item $quoteItem */
        $quoteItem = $observer->getEvent()->getQuoteItem();
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();
        $storeId = $quoteItem->getStoreId();

        if (!$this->insightsHelper->isAddedToCartTracked($storeId) && !$this->insightsHelper->isOrderPlacedTracked($storeId)) {
            return;
        }

        $userClient = $this->insightsHelper->getUserInsightsClient();

        if ($this->configHelper->isClickConversionAnalyticsEnabled($storeId) && $product->hasData('queryId')) {
            $conversionAnalyticsMode = $this->configHelper->getConversionAnalyticsMode($storeId);
            switch ($conversionAnalyticsMode) {
                case 'place_order':
                    $quoteItem->setData('algoliasearch_query_param', $product->getData('queryId'));
                    break;
                case 'add_to_cart':
                    try {
                        $userClient->convertedObjectIDsAfterSearch(
                            __('Added to Cart'),
                            $this->dataHelper->getIndexName('_products', $storeId),
                            [$product->getId()],
                            $product->getData('queryId')
                        );
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                    }
            }
        } else {
            try {
                $userClient->convertedObjectIDs(
                    __('Added to Cart'),
                    $this->dataHelper->getIndexName('_products', $storeId),
                    [$product->getId()]
                );
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
