<?php

namespace Algolia\AlgoliaSearch\Model\Observer\Insights;

use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Algolia\AlgoliaSearch\Helper\InsightsHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;

class CheckoutCartProductAddBefore implements ObserverInterface
{
    /** @var ConfigHelper */
    private $configHelper;

    /** @var InsightsHelper */
    private $insightsHelper;

    /** @var SessionManagerInterface */
    private $_coreSession;

    /**
     * @param ConfigHelper $configHelper
     * @param InsightsHelper $insightsHelper
     * @param SessionManagerInterface $coreSession
     */
    public function __construct(
        ConfigHelper $configHelper,
        InsightsHelper $insightsHelper,
        SessionManagerInterface $coreSession
    ) {
        $this->configHelper = $configHelper;
        $this->insightsHelper = $insightsHelper;
        $this->_coreSession = $coreSession;
    }

    /**
     * @param Observer $observer
     * ['info' => $requestInfo, 'product' => $product]
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();
        $requestInfo = $observer->getEvent()->getInfo();

        if (isset($requestInfo['queryID']) && $requestInfo['queryID'] != '') {
            $this->_coreSession->start();
            $this->_coreSession->setQueryId($requestInfo['queryID']);
            $product->setData('queryId', $requestInfo['queryID']);
        }
    }
}
