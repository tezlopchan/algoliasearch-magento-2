<?php

namespace Algolia\AlgoliaSearch\Setup\Patch\Data;

use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Mview\View\SubscriptionFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateMviewPatch implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    private $indexerFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

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
        /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
        $indexer = $this->indexerFactory->create()->load('algolia_products');
        $subscriptionInstance = $this->subscriptionFactory->create(
            [
                'view' => $indexer->getView(),
                'tableName' => 'catalog_product_index_price',
                'columnName' => 'entity_id',
            ]
        );
        $subscriptionInstance->remove();
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
    public static function getVersion()
    {
        return '1.12.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
