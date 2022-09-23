<?php

namespace Algolia\AlgoliaSearch\Observer\Insights;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CheckoutCartProductAddBefore implements ObserverInterface
{
    /**
     * @param Observer $observer
     * ['info' => $requestInfo, 'product' => $product]
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();
        $requestInfo = $observer->getEvent()->getInfo();

        if (isset($requestInfo['queryID']) && $requestInfo['queryID'] != '') {
            $product->setData('queryId', $requestInfo['queryID']);
        }
    }
}
