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

    protected function setFinalGroupPricesBundle($field, $currencyCode, $min, $max, $dashedFormat)
    {
        /** @var Group $group */
        foreach ($this->groups as $group) {
            $groupId = (int) $group->getData('customer_group_id');

            if ($this->customData[$field][$currencyCode]['group_' . $groupId] == 0) {
                $this->customData[$field][$currencyCode]['group_' . $groupId] = $min;

                if ($min === $max) {
                    $this->customData[$field][$currencyCode]['group_' . $groupId . '_formated'] =
                        $this->customData[$field][$currencyCode]['default_formated'];
                } else {
                    $this->customData[$field][$currencyCode]['group_' . $groupId . '_formated'] = $dashedFormat;
                }
            }
        }
    }
}
