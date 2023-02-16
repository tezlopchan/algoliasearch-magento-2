<?php

namespace Algolia\AlgoliaSearch\Helper;

use Algolia\AlgoliaSearch\Exception\CategoryReindexingException;
use Algolia\AlgoliaSearch\Exception\ProductReindexingException;
use Algolia\AlgoliaSearch\Helper\Entity\AdditionalSectionHelper;
use Algolia\AlgoliaSearch\Helper\Entity\CategoryHelper;
use Algolia\AlgoliaSearch\Helper\Entity\PageHelper;
use Algolia\AlgoliaSearch\Helper\Entity\ProductHelper;
use Algolia\AlgoliaSearch\Helper\Entity\SuggestionHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Search\Model\Query;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

class Data
{
    /**
     * @var AlgoliaHelper
     */
    protected $algoliaHelper;
    /**
     * @var PageHelper
     */
    protected $pageHelper;
    /**
     * @var CategoryHelper
     */
    protected $categoryHelper;
    /**
     * @var ProductHelper
     */
    protected $productHelper;
    /**
     * @var SuggestionHelper
     */
    protected $suggestionHelper;
    /**
     * @var AdditionalSectionHelper
     */
    protected $additionalSectionHelper;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var ConfigHelper
     */
    protected $configHelper;
    /**
     * @var Emulation
     */
    protected $emulation;
    /**
     * @var ResourceConnection
     */
    protected $resource;
    /**
     * @var ManagerInterface
     */
    protected $eventManager;
    /**
     * @var ScopeCodeResolver
     */
    protected $scopeCodeResolver;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    protected $emulationRuns = false;

    /**
     * @param AlgoliaHelper $algoliaHelper
     * @param ConfigHelper $configHelper
     * @param ProductHelper $producthelper
     * @param CategoryHelper $categoryHelper
     * @param PageHelper $pageHelper
     * @param SuggestionHelper $suggestionHelper
     * @param AdditionalSectionHelper $additionalSectionHelper
     * @param Emulation $emulation
     * @param Logger $logger
     * @param ResourceConnection $resource
     * @param ManagerInterface $eventManager
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        AlgoliaHelper $algoliaHelper,
        ConfigHelper $configHelper,
        ProductHelper $producthelper,
        CategoryHelper $categoryHelper,
        PageHelper $pageHelper,
        SuggestionHelper $suggestionHelper,
        AdditionalSectionHelper $additionalSectionHelper,
        Emulation $emulation,
        Logger $logger,
        ResourceConnection $resource,
        ManagerInterface $eventManager,
        ScopeCodeResolver $scopeCodeResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->algoliaHelper = $algoliaHelper;
        $this->pageHelper = $pageHelper;
        $this->categoryHelper = $categoryHelper;
        $this->productHelper = $producthelper;
        $this->suggestionHelper = $suggestionHelper;
        $this->additionalSectionHelper = $additionalSectionHelper;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
        $this->emulation = $emulation;
        $this->resource = $resource;
        $this->eventManager = $eventManager;
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * @return ConfigHelper
     */
    public function getConfigHelper()
    {
        return $this->configHelper;
    }

    /**
     * @param $storeId
     * @param $ids
     * @param $indexName
     * @return void
     */
    public function deleteObjects($storeId, $ids, $indexName)
    {
        if ($this->isIndexingEnabled($storeId) === false) {
            return;
        }
        $this->algoliaHelper->deleteObjects($ids, $indexName);
    }

    /**
     * @param string $query
     * @param int $storeId
     * @param array|null $searchParams
     * @param string|null $targetedIndex
     *
     * @return array
     */
    public function getSearchResult($query, $storeId, $searchParams = null, $targetedIndex = null)
    {
        $indexName = $targetedIndex !== null ?
            $targetedIndex :
            $this->getIndexName($this->productHelper->getIndexNameSuffix(), $storeId);

        $numberOfResults = 1000;
        if ($this->configHelper->isInstantEnabled()) {
            $numberOfResults = min($this->configHelper->getNumberOfProductResults($storeId), 1000);
        }

        $facetsToRetrieve = [];
        foreach ($this->configHelper->getFacets($storeId) as $facet) {
            $facetsToRetrieve[] = $facet['attribute'];
        }

        $params = [
            'hitsPerPage'            => $numberOfResults, // retrieve all the hits (hard limit is 1000)
            'attributesToRetrieve'   => 'objectID',
            'attributesToHighlight'  => '',
            'attributesToSnippet'    => '',
            'numericFilters'         => ['visibility_search=1'],
            'removeWordsIfNoResults' => $this->configHelper->getRemoveWordsIfNoResult($storeId),
            'analyticsTags'          => 'backend-search',
            'facets'                 => $facetsToRetrieve,
            'maxValuesPerFacet'      => 100,
        ];

        if (is_array($searchParams)) {
            $params = array_merge($params, $searchParams);
        }

        $answer = $this->algoliaHelper->query($indexName, $query, $params);

        $data = [];

        foreach ($answer['hits'] as $i => $hit) {
            $productId = $hit['objectID'];

            if ($productId) {
                $data[$productId] = [
                    'entity_id' => $productId,
                    'score'     => $numberOfResults - $i,
                ];
            }
        }

        $facetsFromAnswer = $answer['facets'] ?? [];

        return [$data, $answer['nbHits'], $facetsFromAnswer];
    }

    /**
     * @param $storeId
     * @return void
     * @throws \Algolia\AlgoliaSearch\Exceptions\AlgoliaException
     */
    public function rebuildStoreAdditionalSectionsIndex($storeId)
    {
        if ($this->isIndexingEnabled($storeId) === false) {
            return;
        }

        $additionalSections = $this->configHelper->getAutocompleteSections();

        $protectedSections = ['products', 'categories', 'pages', 'suggestions'];
        foreach ($additionalSections as $section) {
            if (in_array($section['name'], $protectedSections, true)) {
                continue;
            }

            $indexName = $this->getIndexName($this->additionalSectionHelper->getIndexNameSuffix(), $storeId);
            $indexName = $indexName . '_' . $section['name'];

            $attributeValues = $this->additionalSectionHelper->getAttributeValues($storeId, $section);

            foreach (array_chunk($attributeValues, 100) as $chunk) {
                $this->algoliaHelper->addObjects($chunk, $indexName . '_tmp');
            }

            $this->algoliaHelper->copyQueryRules($indexName, $indexName . '_tmp');
            $this->algoliaHelper->moveIndex($indexName . '_tmp', $indexName);

            $this->algoliaHelper->setSettings($indexName, $this->additionalSectionHelper->getIndexSettings($storeId));
        }
    }

    /**
     * @param $storeId
     * @param array|null $pageIds
     * @return void
     * @throws \Algolia\AlgoliaSearch\Exceptions\AlgoliaException
     */
    public function rebuildStorePageIndex($storeId, array $pageIds = null)
    {
        if ($this->isIndexingEnabled($storeId) === false) {
            return;
        }

        $indexName = $this->getIndexName($this->pageHelper->getIndexNameSuffix(), $storeId);

        $this->startEmulation($storeId);

        $pages = $this->pageHelper->getPages($storeId, $pageIds);

        $this->stopEmulation();

        // if there are pageIds defined, do not index to _tmp
        $isFullReindex = (!$pageIds);

        if (isset($pages['toIndex']) && count($pages['toIndex'])) {
            $pagesToIndex = $pages['toIndex'];
            $toIndexName = $indexName . ($isFullReindex ? '_tmp' : '');

            foreach (array_chunk($pagesToIndex, 100) as $chunk) {
                try {
                    $this->algoliaHelper->addObjects($chunk, $toIndexName);
                } catch (\Exception $e) {
                    $this->logger->log($e->getMessage());
                    continue;
                }
            }
        }

        if (!$isFullReindex && isset($pages['toRemove']) && count($pages['toRemove'])) {
            $pagesToRemove = $pages['toRemove'];
            foreach (array_chunk($pagesToRemove, 100) as $chunk) {
                try {
                    $this->algoliaHelper->deleteObjects($chunk, $indexName);
                } catch (\Exception $e) {
                    $this->logger->log($e->getMessage());
                    continue;
                }
            }
        }

        if ($isFullReindex) {
            $this->algoliaHelper->copyQueryRules($indexName, $indexName . '_tmp');
            $this->algoliaHelper->moveIndex($indexName . '_tmp', $indexName);
        }
        $this->algoliaHelper->setSettings($indexName, $this->pageHelper->getIndexSettings($storeId));
    }

    /**
     * @param $storeId
     * @param $categoryIds
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function rebuildStoreCategoryIndex($storeId, $categoryIds = null)
    {
        if ($this->isIndexingEnabled($storeId) === false) {
            return;
        }

        $this->startEmulation($storeId);

        try {
            $collection = $this->categoryHelper->getCategoryCollectionQuery($storeId, $categoryIds);

            $size = $collection->getSize();
            if (!empty($categoryIds)) {
                $size = max(count($categoryIds), $size);
            }

            if ($size > 0) {
                $pages = ceil($size / $this->configHelper->getNumberOfElementByPage());
                $page = 1;
                while ($page <= $pages) {
                    $this->rebuildStoreCategoryIndexPage(
                        $storeId,
                        $collection,
                        $page,
                        $this->configHelper->getNumberOfElementByPage(),
                        $categoryIds
                    );
                    $page++;
                }
                unset($indexData);
            }
        } catch (\Exception $e) {
            $this->stopEmulation();
            throw $e;
        }
        $this->stopEmulation();
    }

    /**
     * @param $storeId
     * @return void
     */
    public function rebuildStoreSuggestionIndex($storeId)
    {
        if ($this->isIndexingEnabled($storeId) === false) {
            return;
        }

        $collection = $this->suggestionHelper->getSuggestionCollectionQuery($storeId);
        $size = $collection->getSize();

        if ($size > 0) {
            $pages = ceil($size / $this->configHelper->getNumberOfElementByPage());
            $collection->clear();
            $page = 1;

            while ($page <= $pages) {
                $this->rebuildStoreSuggestionIndexPage(
                    $storeId,
                    $collection,
                    $page,
                    $this->configHelper->getNumberOfElementByPage()
                );
                $page++;
            }
            unset($indexData);
        }
        $this->moveStoreSuggestionIndex($storeId);
    }

    /**
     * @param $storeId
     * @return void
     */
    public function moveStoreSuggestionIndex($storeId)
    {
        if ($this->isIndexingEnabled($storeId) === false) {
            return;
        }

        $indexNameSuffix = $this->suggestionHelper->getIndexNameSuffix();
        $tmpIndexName = $this->getIndexName($indexNameSuffix, $storeId, true);
        $indexName = $this->getIndexName($indexNameSuffix, $storeId);
        $this->algoliaHelper->copyQueryRules($indexName, $tmpIndexName);
        $this->algoliaHelper->moveIndex($tmpIndexName, $indexName);
    }

    /**
     * @param $storeId
     * @param $productIds
     * @return void
     * @throws \Exception
     */
    public function rebuildStoreProductIndex($storeId, $productIds)
    {
        if ($this->isIndexingEnabled($storeId) === false) {
            return;
        }

        $this->startEmulation($storeId);
        $this->logger->start('Indexing');
        try {
            $this->logger->start('ok');
            $collection = $this->productHelper->getProductCollectionQuery($storeId, $productIds);
            $size = $collection->getSize();
            if (!empty($productIds)) {
                $size = max(count($productIds), $size);
            }
            $this->logger->log('Store ' . $this->logger->getStoreName($storeId) . ' collection size : ' . $size);
            if ($size > 0) {
                $pages = ceil($size / $this->configHelper->getNumberOfElementByPage());
                $collection->clear();
                $page = 1;
                while ($page <= $pages) {
                    $this->rebuildStoreProductIndexPage(
                        $storeId,
                        $collection,
                        $page,
                        $this->configHelper->getNumberOfElementByPage(),
                        null,
                        $productIds
                    );
                    $page++;
                }
            }
        } catch (\Exception $e) {
            $this->stopEmulation();
            throw $e;
        }
        $this->logger->stop('Indexing');
        $this->stopEmulation();
    }

    /**
     * @param $storeId
     * @param $productIds
     * @param $page
     * @param $pageSize
     * @param $useTmpIndex
     * @return void
     */
    public function rebuildProductIndex($storeId, $productIds, $page, $pageSize, $useTmpIndex)
    {
        if ($this->isIndexingEnabled($storeId) === false) {
            return;
        }
        $collection = $this->productHelper->getProductCollectionQuery($storeId, null, $useTmpIndex);
        $this->rebuildStoreProductIndexPage($storeId, $collection, $page, $pageSize, null, $productIds, $useTmpIndex);
    }

    /**
     * @param $storeId
     * @param $page
     * @param $pageSize
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function rebuildCategoryIndex($storeId, $page, $pageSize)
    {
        if ($this->isIndexingEnabled($storeId) === false) {
            return;
        }

        $this->startEmulation($storeId);
        $collection = $this->categoryHelper->getCategoryCollectionQuery($storeId, null);
        $this->rebuildStoreCategoryIndexPage($storeId, $collection, $page, $pageSize);
        $this->stopEmulation();
    }

    /**
     * @param $storeId
     * @param $collectionDefault
     * @param $page
     * @param $pageSize
     * @return void
     */
    public function rebuildStoreSuggestionIndexPage($storeId, $collectionDefault, $page, $pageSize)
    {
        if ($this->isIndexingEnabled($storeId) === false) {
            return;
        }

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection */
        $collection = clone $collectionDefault;
        $collection->setCurPage($page)->setPageSize($pageSize);
        $collection->load();
        $indexName = $this->getIndexName($this->suggestionHelper->getIndexNameSuffix(), $storeId, true);
        $indexData = [];

        /** @var Query $suggestion */
        foreach ($collection as $suggestion) {
            $suggestion->setStoreId($storeId);
            $suggestionObject = $this->suggestionHelper->getObject($suggestion);
            if (mb_strlen($suggestionObject['query']) >= 3) {
                array_push($indexData, $suggestionObject);
            }
        }

        if (count($indexData) > 0) {
            $this->algoliaHelper->addObjects($indexData, $indexName);
        }
        unset($indexData);
        $collection->walk('clearInstance');
        $collection->clear();
        unset($collection);
    }

    public function rebuildStoreCategoryIndexPage($storeId, $collection, $page, $pageSize, $categoryIds = null)
    {
        if ($this->isIndexingEnabled($storeId) === false) {
            return;
        }
        $collection->setCurPage($page)->setPageSize($pageSize);
        $collection->load();
        $indexName = $this->getIndexName($this->categoryHelper->getIndexNameSuffix(), $storeId);
        $indexData = $this->getCategoryRecords($storeId, $collection, $categoryIds);
        if (!empty($indexData['toIndex'])) {
            $this->logger->start('ADD/UPDATE TO ALGOLIA');
            $this->algoliaHelper->addObjects($indexData['toIndex'], $indexName);
            $this->logger->log('Product IDs: ' . implode(', ', array_keys($indexData['toIndex'])));
            $this->logger->stop('ADD/UPDATE TO ALGOLIA');
        }

        if (!empty($indexData['toRemove'])) {
            $toRealRemove = $this->getIdsToRealRemove($indexName, $indexData['toRemove']);
            if (!empty($toRealRemove)) {
                $this->logger->start('REMOVE FROM ALGOLIA');
                $this->algoliaHelper->deleteObjects($toRealRemove, $indexName);
                $this->logger->log('Category IDs: ' . implode(', ', $toRealRemove));
                $this->logger->stop('REMOVE FROM ALGOLIA');
            }
        }
        unset($indexData);
        $collection->walk('clearInstance');
        $collection->clear();
        unset($collection);
    }

    /**
     * @param $storeId
     * @param $collection
     * @param $potentiallyDeletedProductsIds
     * @return array
     * @throws \Exception
     */
    protected function getProductsRecords($storeId, $collection, $potentiallyDeletedProductsIds = null)
    {
        $productsToIndex = [];
        $productsToRemove = [];

        // In $potentiallyDeletedProductsIds there might be IDs of deleted products which will not be in a collection
        if (is_array($potentiallyDeletedProductsIds)) {
            $potentiallyDeletedProductsIds = array_combine(
                $potentiallyDeletedProductsIds,
                $potentiallyDeletedProductsIds
            );
        }

        $this->logger->start('CREATE RECORDS ' . $this->logger->getStoreName($storeId));
        $this->logger->log(count($collection) . ' product records to create');
        $salesData = $this->getSalesData($storeId, $collection);
        $transport = new ProductDataArray();
        $this->eventManager->dispatch(
            'algolia_product_collection_add_additional_data',
            ['collection' => $collection, 'store_id' => $storeId, 'additional_data' => $transport]
        );

        /** @var Product $product */
        foreach ($collection as $product) {
            $product->setStoreId($storeId);
            $product->setPriceCalculation(false);
            $productId = $product->getId();
            // If $productId is in the collection, remove it from $potentiallyDeletedProductsIds
            // so it's not removed without check
            if (isset($potentiallyDeletedProductsIds[$productId])) {
                unset($potentiallyDeletedProductsIds[$productId]);
            }

            if (isset($productsToIndex[$productId]) || isset($productsToRemove[$productId])) {
                continue;
            }

            try {
                $this->productHelper->canProductBeReindexed($product, $storeId);
            } catch (ProductReindexingException $e) {
                $productsToRemove[$productId] = $productId;
                continue;
            }

            if (isset($salesData[$productId])) {
                $product->setData('ordered_qty', $salesData[$productId]['ordered_qty']);
                $product->setData('total_ordered', $salesData[$productId]['total_ordered']);
            }

            if ($additionalData = $transport->getItem($productId)) {
                foreach ($additionalData as $key => $value) {
                    $product->setData($key, $value);
                }
            }

            $productsToIndex[$productId] = $this->productHelper->getObject($product);
        }

        if (is_array($potentiallyDeletedProductsIds)) {
            $productsToRemove = array_merge($productsToRemove, $potentiallyDeletedProductsIds);
        }

        $this->logger->stop('CREATE RECORDS ' . $this->logger->getStoreName($storeId));
        return [
            'toIndex' => $productsToIndex,
            'toRemove' => array_unique($productsToRemove),
        ];
    }

    /**
     * @param int $storeId
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $collection
     * @param array|null $potentiallyDeletedCategoriesIds
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return array
     */
    protected function getCategoryRecords($storeId, $collection, $potentiallyDeletedCategoriesIds = null)
    {
        $categoriesToIndex = [];
        $categoriesToRemove = [];

        // In $potentiallyDeletedCategoriesIds there might be IDs of deleted products which will not be in a collection
        if (is_array($potentiallyDeletedCategoriesIds)) {
            $potentiallyDeletedCategoriesIds = array_combine(
                $potentiallyDeletedCategoriesIds,
                $potentiallyDeletedCategoriesIds
            );
        }

        /** @var Category $category */
        foreach ($collection as $category) {
            $category->setStoreId($storeId);
            $categoryId = $category->getId();
            // If $categoryId is in the collection, remove it from $potentiallyDeletedProductsIds
            // so it's not removed without check
            if (isset($potentiallyDeletedCategoriesIds[$categoryId])) {
                unset($potentiallyDeletedCategoriesIds[$categoryId]);
            }

            if (isset($categoriesToIndex[$categoryId]) || isset($categoriesToRemove[$categoryId])) {
                continue;
            }

            try {
                $this->categoryHelper->canCategoryBeReindexed($category, $storeId);
            } catch (CategoryReindexingException $e) {
                $categoriesToRemove[$categoryId] = $categoryId;
                continue;
            }

            $categoriesToIndex[$categoryId] = $this->categoryHelper->getObject($category);
        }

        if (is_array($potentiallyDeletedCategoriesIds)) {
            $categoriesToRemove = array_merge($categoriesToRemove, $potentiallyDeletedCategoriesIds);
        }

        return [
            'toIndex' => $categoriesToIndex,
            'toRemove' => array_unique($categoriesToRemove),
        ];
    }

    /**
     * @param $storeId
     * @param $collectionDefault
     * @param $page
     * @param $pageSize
     * @param $emulationInfo
     * @param $productIds
     * @param $useTmpIndex
     * @return void
     * @throws \Exception
     */
    public function rebuildStoreProductIndexPage(
        $storeId,
        $collectionDefault,
        $page,
        $pageSize,
        $emulationInfo = null,
        $productIds = null,
        $useTmpIndex = false
    ) {
        if ($this->isIndexingEnabled($storeId) === false) {
            return;
        }

        $wrapperLogMessage = 'rebuildStoreProductIndexPage: ' . $this->logger->getStoreName($storeId) . ',
            page ' . $page . ',
            pageSize ' . $pageSize;
        $this->logger->start($wrapperLogMessage);
        if ($emulationInfo === null) {
            $this->startEmulation($storeId);
        }
        $additionalAttributes = $this->configHelper->getProductAdditionalAttributes($storeId);

        /** @var Collection $collection */
        $collection = clone $collectionDefault;
        $collection->setCurPage($page)->setPageSize($pageSize);
        $collection->addCategoryIds();
        $collection->addUrlRewrite();

        if ($this->productHelper->isAttributeEnabled($additionalAttributes, 'rating_summary')) {
            $reviewTableName = $this->resource->getTableName('review_entity_summary');
            $collection
                ->getSelect()
                ->columns('(SELECT MAX(rating_summary) FROM ' . $reviewTableName . ' AS o WHERE o.entity_pk_value = e.entity_id AND o.store_id = ' . $storeId . ') as rating_summary');
        }

        $this->eventManager->dispatch(
            'algolia_before_products_collection_load',
            ['collection' => $collection, 'store' => $storeId]
        );
        $logMessage = 'LOADING: ' . $this->logger->getStoreName($storeId) . ',
            collection page: ' . $page . ',
            pageSize: ' . $pageSize;
        $this->logger->start($logMessage);
        $collection->load();
        $this->logger->log('Loaded ' . count($collection) . ' products');
        $this->logger->stop($logMessage);
        $indexName = $this->getIndexName($this->productHelper->getIndexNameSuffix(), $storeId, $useTmpIndex);
        $indexData = $this->getProductsRecords($storeId, $collection, $productIds);
        if (!empty($indexData['toIndex'])) {
            $this->logger->start('ADD/UPDATE TO ALGOLIA');
            $this->algoliaHelper->addObjects($indexData['toIndex'], $indexName);
            $this->logger->log('Product IDs: ' . implode(', ', array_keys($indexData['toIndex'])));
            $this->logger->stop('ADD/UPDATE TO ALGOLIA');
        }

        if (!empty($indexData['toRemove'])) {
            $toRealRemove = $this->getIdsToRealRemove($indexName, $indexData['toRemove']);
            if (!empty($toRealRemove)) {
                $this->logger->start('REMOVE FROM ALGOLIA');
                $this->algoliaHelper->deleteObjects($toRealRemove, $indexName);
                $this->logger->log('Product IDs: ' . implode(', ', $toRealRemove));
                $this->logger->stop('REMOVE FROM ALGOLIA');
            }
        }
        unset($indexData);
        $collection->walk('clearInstance');
        $collection->clear();
        unset($collection);
        if ($emulationInfo === null) {
            $this->stopEmulation();
        }
        $this->logger->stop($wrapperLogMessage);
    }

    /**
     * @param $storeId
     * @return void
     * @throws \Exception
     */
    public function startEmulation($storeId)
    {
        if ($this->emulationRuns === true) {
            return;
        }

        $this->logger->start('START EMULATION');
        $this->emulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        $this->scopeCodeResolver->clean();
        $this->emulationRuns = true;
        $this->logger->stop('START EMULATION');
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function stopEmulation()
    {
        $this->logger->start('STOP EMULATION');
        $this->emulation->stopEnvironmentEmulation();
        $this->emulationRuns = false;
        $this->logger->stop('STOP EMULATION');
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function isIndexingEnabled($storeId = null)
    {
        if ($this->configHelper->isEnabledBackend($storeId) === false) {
            $this->logger->log('INDEXING IS DISABLED FOR ' . $this->logger->getStoreName($storeId));
            return false;
        }
        return true;
    }

    /**
     * @param $indexName
     * @param $idsToRemove
     * @return array|mixed
     */
    protected function getIdsToRealRemove($indexName, $idsToRemove)
    {
        if (count($idsToRemove) === 1) {
            return $idsToRemove;
        }

        $toRealRemove = [];
        $idsToRemove = array_map('strval', $idsToRemove);
        foreach (array_chunk($idsToRemove, 1000) as $chunk) {
            $objects = $this->algoliaHelper->getObjects($indexName, $chunk);
            foreach ($objects['results'] as $object) {
                if (isset($object['objectID'])) {
                    $toRealRemove[] = $object['objectID'];
                }
            }
        }
        return $toRealRemove;
    }

    /**
     * @param $storeId
     * @param Collection $collection
     * @return array
     */
    protected function getSalesData($storeId, Collection $collection)
    {
        $additionalAttributes = $this->configHelper->getProductAdditionalAttributes($storeId);
        if ($this->productHelper->isAttributeEnabled($additionalAttributes, 'ordered_qty') === false
            && $this->productHelper->isAttributeEnabled($additionalAttributes, 'total_ordered') === false) {
            return [];
        }

        $salesData = [];
        $ids = $collection->getColumnValues('entity_id');
        if (count($ids)) {
            $ordersTableName = $this->resource->getTableName('sales_order_item');
            try {
                $salesConnection = $this->resource->getConnectionByName('sales');
            } catch (\DomainException $e) {
                $salesConnection = $this->resource->getConnection();
            }
            $select = $salesConnection->select()
                ->from($ordersTableName, [])
                ->columns('product_id')
                ->columns(['ordered_qty' => new \Zend_Db_Expr('SUM(qty_ordered)')])
                ->columns(['total_ordered' => new \Zend_Db_Expr('SUM(row_total)')])
                ->where('product_id IN (?)', $ids)
                ->group('product_id');
            $salesData = $salesConnection->fetchAll($select, [], \PDO::FETCH_GROUP|\PDO::FETCH_ASSOC|\PDO::FETCH_UNIQUE);
        }
        return $salesData;
    }

    /**
     * @param $storeId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteInactiveProducts($storeId)
    {
        $indexName = $this->getIndexName($this->productHelper->getIndexNameSuffix(), $storeId);
        $index = $this->algoliaHelper->getIndex($indexName);
        $objectIds = [];
        $counter = 0;
        $browseOptions = [
            'query' => '',
            'attributesToRetrieve' => ['objectID'],
        ];
        foreach ($index->browseObjects($browseOptions) as $hit) {
            $objectIds[] = $hit['objectID'];
            $counter++;
            if ($counter === 1000) {
                $this->deleteInactiveIds($storeId, $objectIds, $indexName);
                $objectIds = [];
                $counter = 0;
            }
        }
        if (!empty($objectIds)) {
            $this->deleteInactiveIds($storeId, $objectIds, $indexName);
        }
    }

    /**
     * @param $indexSuffix
     * @param $storeId
     * @param $tmp
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIndexName($indexSuffix, $storeId = null, $tmp = false)
    {
        return $this->getBaseIndexName($storeId) . $indexSuffix . ($tmp ? '_tmp' : '');
    }

    /**
     * @param $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseIndexName($storeId = null)
    {
        return $this->configHelper->getIndexPrefix($storeId) . $this->storeManager->getStore($storeId)->getCode();
    }

    /**
     * @return array
     */
    public function getIndexDataByStoreIds()
    {
        $indexNames = [];
        $indexNames[0] = [
            'indexName' => $this->getBaseIndexName(),
            'priceKey' => '.' . $this->configHelper->getCurrencyCode() . '.default',
        ];
        foreach ($this->storeManager->getStores() as $store) {
            $indexNames[$store->getId()] = [
                'indexName' => $this->getBaseIndexName($store->getId()),
                'priceKey' => '.' . $store->getCurrentCurrencyCode($store->getId()) . '.default',
            ];
        }
        return $indexNames;
    }

    /**
     * @param $storeId
     * @param $objectIds
     * @param $indexName
     * @return void
     */
    protected function deleteInactiveIds($storeId, $objectIds, $indexName)
    {
        $collection = $this->productHelper->getProductCollectionQuery($storeId, $objectIds);
        $dbIds = $collection->getAllIds();
        $collection = null;
        $idsToDeleteFromAlgolia = array_diff($objectIds, $dbIds);
        $this->algoliaHelper->deleteObjects($idsToDeleteFromAlgolia, $indexName);
    }
}