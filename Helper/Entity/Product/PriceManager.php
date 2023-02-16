<?php

namespace Algolia\AlgoliaSearch\Helper\Entity\Product;

use Algolia\AlgoliaSearch\Helper\Entity\Product\PriceManager\Bundle as PriceManagerBundle;
use Algolia\AlgoliaSearch\Helper\Entity\Product\PriceManager\Configurable as PriceManagerConfigurable;
use Algolia\AlgoliaSearch\Helper\Entity\Product\PriceManager\Downloadable as PriceManagerDownloadable;
use Algolia\AlgoliaSearch\Helper\Entity\Product\PriceManager\Grouped as PriceManagerGrouped;
use Algolia\AlgoliaSearch\Helper\Entity\Product\PriceManager\Simple as PriceManagerSimple;
use Algolia\AlgoliaSearch\Helper\Entity\Product\PriceManager\Virtual as PriceManagerVirtual;
use Magento\Catalog\Model\Product;

class PriceManager
{
    /**
     * @var PriceManagerSimple
     */
    protected $priceManagerSimple;

    /**
     * @var PriceManagerVirtual
     */
    protected $priceManagerVirtual;
    /**
     * @var PriceManagerDownloadable
     */
    protected $priceManagerDownloadable;
    /**
     * @var PriceManagerConfigurable
     */
    protected $priceManagerConfigurable;
    /**
     * @var PriceManagerBundle
     */
    protected $priceManagerBundle;
    /**
     * @var PriceManagerGrouped
     */
    protected $priceManagerGrouped;

    /**
     * @param PriceManagerSimple $priceManagerSimple
     * @param PriceManagerVirtual $priceManagerVirtual
     * @param PriceManagerDownloadable $priceManagerDownloadable
     * @param PriceManagerConfigurable $priceManagerConfigurable
     * @param PriceManagerBundle $priceManagerBundle
     * @param PriceManagerGrouped $priceManagerGrouped
     */
    public function __construct(
        PriceManagerSimple $priceManagerSimple,
        PriceManagerVirtual $priceManagerVirtual,
        PriceManagerDownloadable $priceManagerDownloadable,
        PriceManagerConfigurable $priceManagerConfigurable,
        PriceManagerBundle $priceManagerBundle,
        PriceManagerGrouped $priceManagerGrouped
    ) {
        $this->priceManagerSimple = $priceManagerSimple;
        $this->priceManagerVirtual = $priceManagerVirtual;
        $this->priceManagerDownloadable = $priceManagerDownloadable;
        $this->priceManagerConfigurable = $priceManagerConfigurable;
        $this->priceManagerBundle = $priceManagerBundle;
        $this->priceManagerGrouped = $priceManagerGrouped;
    }

    /**
     * @param $customData
     * @param Product $product
     * @param $subProducts
     * @return mixed
     */
    public function addPriceDataByProductType($customData, Product $product, $subProducts)
    {
        $priceManager = 'priceManager' . ucfirst($product->getTypeId());
        if (!property_exists($this, $priceManager)) {
            $priceManager = 'priceManagerSimple';
        }
        return $this->{$priceManager}->addPriceData($customData, $product, $subProducts);
    }
}
