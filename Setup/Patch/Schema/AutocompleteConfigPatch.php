<?php

namespace Algolia\AlgoliaSearch\Setup\Patch\Schema;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class AutocompleteConfigPatch implements SchemaPatchInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ConfigInterface $config
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ConfigInterface $config,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->config = $config;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        $table = $connection->getTableName('core_config_data');
        $path = 'algoliasearch_advanced/advanced/autocomplete_selector';
        $value = 'top.search';
        $connection->query('UPDATE ' . $table . ' SET value = "' . $value . '" WHERE path = "' . $path . '"');
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}
