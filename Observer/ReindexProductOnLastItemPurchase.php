<?php
declare(strict_types=1);

namespace Algolia\AlgoliaSearch\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryShipping\Model\GetItemsToDeductFromShipment;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;

class ReindexProductOnLastItemPurchase implements ObserverInterface
{
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var GetItemsToDeductFromShipment
     */
    private $getItemsToDeductFromShipment;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;


    private $indexer;

    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        IsSingleSourceModeInterface $isSingleSourceMode,
        IndexerRegistry $indexerRegistry,
        GetSourceItemsBySkuInterface $getSourceItemsBySku
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->indexer = $indexerRegistry->get('algolia_products');
    }
    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $observer->getEvent()->getShipment();
        if ($shipment->getOrigData('entity_id')) {
            return;
        }

        if (!empty($shipment->getExtensionAttributes())
            && !empty($shipment->getExtensionAttributes()->getSourceCode())) {
            $sourceCode = $shipment->getExtensionAttributes()->getSourceCode();
        } elseif ($this->isSingleSourceMode->execute()) {
            $sourceCode = $this->defaultSourceProvider->getCode();
        }

        foreach ($shipment->getAllItems() as $item) {
            $sourceItemList = $this->getSourceItemsBySku->execute($item->getSku());
            foreach ($sourceItemList as $source) {
                if ($source->getSourceCode() == $sourceCode) {
                    if ($source->getQuantity() < 1) {
                        $this->indexer->reindexRow($item->getProductId());
                    }
                }
            }
        }
    }
}
