<?php

namespace Algolia\AlgoliaSearch\Setup\Patch\Data;

use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Mview\View\SubscriptionFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateMviewPatch implements DataPatchInterface
{
    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    /**
     * @var IndexerInterfaceFactory
     */
    private $indexerFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param SubscriptionFactory $subscriptionFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param IndexerInterfaceFactory $indexerFactory
     */
    public function __construct(
        SubscriptionFactory $subscriptionFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        IndexerInterfaceFactory $indexerFactory
    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->indexerFactory = $indexerFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $indexer = $this->indexerFactory->create()->load('algolia_products');
        try {
            $subscriptionInstance = $this->subscriptionFactory->create(
                [
                    'view' => $indexer->getView(),
                    'tableName' => 'catalog_product_index_price',
                    'columnName' => 'entity_id',
                ]
            );
            $subscriptionInstance->remove();
        } catch (\Exception $e) {
            // Skip
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
