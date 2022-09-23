<?php

namespace Algolia\AlgoliaSearch\Observer\Insights;

use Algolia\AlgoliaSearch\Helper\Configuration\PersonalizationHelper;
use Algolia\AlgoliaSearch\Helper\Data;
use Algolia\AlgoliaSearch\Helper\InsightsHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Wishlist\Model\Item;

class WishlistProductAddAfter implements ObserverInterface
{
    /** @var Data */
    protected Data $dataHelper;

    /** @var PersonalizationHelper */
    protected PersonalizationHelper $personalisationHelper;

    /** @var InsightsHelper */
    protected InsightsHelper $insightsHelper;

    /**
     * CheckoutCartProductAddAfter constructor.
     *
     * @param Data $dataHelper
     * @param PersonalizationHelper $personalisationHelper
     * @param InsightsHelper $insightsHelper
     */
    public function __construct(
        Data $dataHelper,
        PersonalizationHelper $personalisationHelper,
        InsightsHelper $insightsHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->personalisationHelper = $personalisationHelper;
        $this->insightsHelper = $insightsHelper;
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

        $userClient->convertedObjectIDs(
            __('Added to Wishlist'),
            $this->dataHelper->getIndexName('_products', $firstItem->getStoreId()),
            $productIds
        );
    }
}
