<?php

namespace Algolia\AlgoliaSearch\Observer;

use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;

class TopSearchTemplate implements ObserverInterface
{
    protected $config;

    /**
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper
    ) {
        $this->config = $configHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Layout $layout */
        $layout = $observer->getData('layout');
        $block = $layout->getBlock($this->config->getAutocompleteSelector());
        if ($block) {
            $block->setTemplate('Algolia_AlgoliaSearch::autocomplete.phtml');
        }
    }
}
