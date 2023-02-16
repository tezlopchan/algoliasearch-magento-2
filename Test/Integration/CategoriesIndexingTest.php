<?php

namespace Algolia\AlgoliaSearch\Test\Integration;

use Algolia\AlgoliaSearch\Model\Indexer\Category;

class CategoriesIndexingTest extends IndexingTestCase
{
    public function testCategories()
    {
        /** @var Category $categoriesIndexer */
        $categoriesIndexer = $this->getObjectManager()->create(Category::class);
        $this->processTest($categoriesIndexer, 'categories', $this->assertValues->expectedCategory);
    }

    public function testDefaultIndexableAttributes()
    {
        $this->setConfig(
            'algoliasearch_categories/categories/category_additional_attributes',
            $this->getSerializer()->serialize([])
        );

        /** @var Category $categoriesIndexer */
        $categoriesIndexer = $this->getObjectManager()->create(Category::class);
        $categoriesIndexer->executeRow(3);

        $this->algoliaHelper->waitLastTask();

        $results = $this->algoliaHelper->getObjects($this->indexPrefix . 'default_categories', ['3']);
        $hit = reset($results['results']);

        $defaultAttributes = [
            'objectID',
            'name',
            'url',
            'path',
            'level',
            'include_in_menu',
            '_tags',
            'popularity',
            'algoliaLastUpdateAtCET',
            'product_count',
        ];

        foreach ($defaultAttributes as $key => $attribute) {
            $this->assertTrue(key_exists($attribute, $hit), 'Category attribute "' . $attribute . '" should be indexed but it is not"');
            unset($hit[$attribute]);
        }

        $extraAttributes = implode(', ', array_keys($hit));
        $this->assertTrue(empty($hit), 'Extra category attributes (' . $extraAttributes . ') are indexed and should not be.');
    }
}
