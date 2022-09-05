<?php

namespace Algolia\AlgoliaSearch\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class TrendingItemType implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'trending_global_item',         'label' => __('Trending Global Items')],
            ['value' => 'trending_items_for_facets',        'label' => __('Trending items for facets')],
        ];
    }
}
