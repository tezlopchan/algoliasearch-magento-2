<?php

namespace Algolia\AlgoliaSearch\Observer\Insights;

use Algolia\AlgoliaSearch\Helper\InsightsHelper;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface
{

    /** @var InsightsHelper */
    protected $insightsHelper;

    /**
     * @param InsightsHelper $insightsHelper
     */
    public function __construct(
        InsightsHelper $insightsHelper
    ) {
        $this->insightsHelper = $insightsHelper;
    }

    /**
     * @param Observer $observer
     * ['customer' => $customer]
     */
    public function execute(Observer $observer)
    {
        /** @var Customer $customer */
        $customer = $observer->getEvent()->getCustomer();

        if ($this->insightsHelper->getPersonalizationHelper()->isPersoEnabled($customer->getStoreId())) {
            $this->insightsHelper->setUserToken($customer);
        }
    }
}
