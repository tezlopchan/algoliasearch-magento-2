<?php

namespace Algolia\AlgoliaSearch\Helper\Entity\Product\PriceManager;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product;

class Bundle extends ProductWithChildren
{
    /**
     * @param Product $product
     * @param $withTax
     * @param $subProducts
     * @param $currencyCode
     * @return array
     */
    protected function getMinMaxPrices(Product $product, $withTax, $subProducts, $currencyCode)
    {
        $min = PHP_INT_MAX;
        $max = 0;

        /** @var Price $priceModel */
        $priceModel = $product->getPriceModel();
        list($min, $max) = $priceModel->getTotalPrices($product, null, $withTax, true);

        if ($currencyCode !== $this->baseCurrencyCode) {
            $min = $this->convertPrice($min, $currencyCode);

            if ($min !== $max) {
                $max = $this->convertPrice($max, $currencyCode);
            }
        }

        return [$min, $max, $min, $max];
    }
}
