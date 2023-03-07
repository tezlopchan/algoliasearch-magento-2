<?php

namespace Algolia\AlgoliaSearch\Observer\Insights;

use Algolia\AlgoliaSearch\Helper\Configuration\PersonalizationHelper;
use Algolia\AlgoliaSearch\Helper\Data;
use Algolia\AlgoliaSearch\Helper\InsightsHelper;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Wishlist\Model\Item;
use Psr\Log\LoggerInterface;

class WishlistProductAddAfter implements ObserverInterface
{
    /** @var Data */
    protected $dataHelper;

    /** @var PersonalizationHelper */
    protected $personalisationHelper;

    /** @var InsightsHelper */
    protected $insightsHelper;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * CheckoutCartProductAddAfter constructor.
     *
     * @param Data $dataHelper
     * @param PersonalizationHelper $personalisationHelper
     * @param InsightsHelper $insightsHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $dataHelper,
        PersonalizationHelper $personalisationHelper,
        InsightsHelper $insightsHelper,
        LoggerInterface $logger
    ) {
        $this->dataHelper = $dataHelper;
        $this->personalisationHelper = $personalisationHelper;
        $this->insightsHelper = $insightsHelper;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * ['order' => $this]
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $items = $observer->getEvent()->getItems();
        /** @var Item $firstItem */
        $firstItem = $items[0];

        if (!$this->personalisationHelper->isPersoEnabled($firstItem->getStoreId())
            || !$this->personalisationHelper->isWishlistAddTracked($firstItem->getStoreId())) {
            return;
        }

        $userClient = $this->insightsHelper->getUserInsightsClient();
        $productIds = [];

        /** @var Item $item */
        foreach ($items as $item) {
            $productIds[] = $item->getProductId();
        }

        try {
            $userClient->convertedObjectIDs(
                __('Added to Wishlist'),
                $this->dataHelper->getIndexName('_products', $firstItem->getStoreId()),
                $productIds
            );
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }
}
