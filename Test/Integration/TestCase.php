<?php

namespace Algolia\AlgoliaSearch\Test\Integration;

use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\Helper\AlgoliaHelper;
use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Algolia\AlgoliaSearch\Setup\Patch\Schema\ConfigPatch;
use Algolia\AlgoliaSearch\Test\Integration\AssertValues\Magento244;
use Algolia\AlgoliaSearch\Test\Integration\AssertValues\Magento_2_01;
use Algolia\AlgoliaSearch\Test\Integration\AssertValues\Magento_2_2;
use Algolia\AlgoliaSearch\Test\Integration\AssertValues\Magento_2_3;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

if (class_exists('PHPUnit\Framework\TestCase')) {
    class_alias('PHPUnit\Framework\TestCase', '\TC');
} else {
    class_alias('\PHPUnit_Framework_TestCase', '\TC');
}

if (class_exists('\Algolia\AlgoliaSearch\Test\Integration\AssertValues_2_01')) {
    class_alias('\Algolia\AlgoliaSearch\Test\Integration\AssertValues_2_01', 'AssertValues');
}

abstract class TestCase extends \TC
{
    /** @var bool */
    private $boostrapped = false;

    /** @var string */
    protected $indexPrefix;

    /** @var AlgoliaHelper */
    protected $algoliaHelper;

    /** @var ConfigHelper */
    protected $configHelper;

    /** @var Magento_2_01|Magento_2_2 */
    protected $assertValues;

    public function setUp(): void
    {
        $this->bootstrap();
    }

    public function tearDown(): void
    {
        $this->clearIndices();
    }

    protected function resetConfigs($configs = [])
    {
        /** @var ConfigPatch $installClass */
        $installClass = $this->getObjectManager()->get(ConfigPatch::class);
        $defaultConfigData = $installClass->getDefaultConfigData();

        foreach ($configs as $config) {
            $value = (string) $defaultConfigData[$config];
            $this->setConfig($config, $value);
        }
    }

    protected function setConfig($path, $value)
    {
        $this->getObjectManager()->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class)->setValue(
            $path,
            $value,
            ScopeInterface::SCOPE_STORE,
            'default'
        );
    }

    protected function clearIndices()
    {
        $indices = $this->algoliaHelper->listIndexes();

        foreach ($indices['items'] as $index) {
            $name = $index['name'];

            if (mb_strpos($name, $this->indexPrefix) === 0) {
                try {
                    $this->algoliaHelper->deleteIndex($name);
                } catch (AlgoliaException $e) {
                    // Might be a replica
                }
            }
        }
    }

    /** @return \Magento\Framework\ObjectManagerInterface */
    protected function getObjectManager()
    {
        return Bootstrap::getObjectManager();
    }

    private function bootstrap()
    {
        if ($this->boostrapped === true) {
            return;
        }

        if (version_compare($this->getMagentoVersion(), '2.2.0', '<')) {
            $this->assertValues = new Magento_2_01();
        } elseif (version_compare($this->getMagentoVersion(), '2.3.0', '<')) {
            $this->assertValues = new Magento_2_2();
        } elseif (version_compare($this->getMagentoVersion(), '2.4.3', '<=')) {
            $this->assertValues = new Magento_2_3();
        } else {
            $this->assertValues = new Magento244();
        }

        $this->algoliaHelper = $this->getObjectManager()->create(AlgoliaHelper::class);

        $this->configHelper = $config = $this->getObjectManager()->create(ConfigHelper::class);

        $this->setConfig('algoliasearch_credentials/credentials/application_id', getenv('ALGOLIA_APPLICATION_ID'));
        $this->setConfig('algoliasearch_credentials/credentials/search_only_api_key', getenv('ALGOLIA_SEARCH_KEY_1') ?: getenv('ALGOLIA_SEARCH_API_KEY'));
        $this->setConfig('algoliasearch_credentials/credentials/api_key', getenv('ALGOLIA_API_KEY'));

        $this->indexPrefix =  'magento2_' . date('Y-m-d_H:i:s') . '_' . (getenv('INDEX_PREFIX') ?: 'circleci_');
        $this->setConfig('algoliasearch_credentials/credentials/index_prefix', $this->indexPrefix);

        $this->boostrapped = true;
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object $object instantiated object that we will run method on
     * @param string $methodName Method name to call
     * @param array $parameters array of parameters to pass into method
     *
     * @throws \ReflectionException
     *
     * @return mixed method return
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    private function getMagentoVersion()
    {
        /** @var ProductMetadataInterface $productMetadata */
        $productMetadata = $this->getObjectManager()->get(ProductMetadataInterface::class);

        return $productMetadata->getVersion();
    }

    protected function getSerializer()
    {
        return $this->getObjectManager()->get(\Magento\Framework\Serialize\SerializerInterface::class);
    }
}
