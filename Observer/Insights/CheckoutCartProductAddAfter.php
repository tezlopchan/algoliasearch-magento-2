<?php

namespace Algolia\AlgoliaSearch\Observer\Insights;

use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Algolia\AlgoliaSearch\Helper\Configuration\PersonalizationHelper;
use Algolia\AlgoliaSearch\Helper\Data;
use Algolia\AlgoliaSearch\Helper\InsightsHelper;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
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

    /** @var PersonalizationHelper */
    private PersonalizationHelper $personalizationHelper;

    /** @var SessionManagerInterface */
    protected SessionManagerInterface $coreSession;

    /**
     * @param Data $dataHelper
     * @param InsightsHelper $insightsHelper
     * @param SessionManagerInterface $coreSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $dataHelper,
        InsightsHelper $insightsHelper,
        SessionManagerInterface $coreSession,
        LoggerInterface $logger
    ) {
        $this->dataHelper = $dataHelper;
        $this->insightsHelper = $insightsHelper;
        $this->logger = $logger;
        $this->coreSession = $coreSession;
        $this->configHelper = $this->insightsHelper->getConfigHelper();
        $this->personalizationHelper = $this->insightsHelper->getPersonalizationHelper();
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
        $queryId = $this->coreSession->getQueryId();
        if ($this->configHelper->isClickConversionAnalyticsEnabled($storeId) && $queryId) {
            $conversionAnalyticsMode = $this->configHelper->getConversionAnalyticsMode($storeId);
            switch ($conversionAnalyticsMode) {
                case 'place_order':
                    $quoteItem->setData('algoliasearch_query_param', $queryId);
                    break;
                case 'add_to_cart':
                    try {
                        $userClient->convertedObjectIDsAfterSearch(
                            __('Added to Cart'),
                            $this->dataHelper->getIndexName('_products', $storeId),
                            [$product->getId()],
                            $queryId
                        );
                    } catch (Exception $e) {
                        $this->logger->critical($e);
                    }
            }
        } 
        
        if ($this->personalizationHelper->isPersoEnabled($storeId) && $this->personalizationHelper->isCartAddTracked($storeId) && (!$this->configHelper->isClickConversionAnalyticsEnabled($storeId) || $this->configHelper->getConversionAnalyticsMode($storeId) != 'add_to_cart')) {
            try {
                $userClient->convertedObjectIDs(
                    __('Added to Cart'),
                    $this->dataHelper->getIndexName('_products', $storeId),
                    [$product->getId()]
                );
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}