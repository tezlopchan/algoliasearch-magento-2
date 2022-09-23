<?php

namespace Algolia\AlgoliaSearch\Model\Checkout;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;

class Cart extends \Magento\Checkout\Model\Cart
{
    /**
     * @param $productInfo
     * @param $requestInfo
     * @return $this|Cart
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addProduct($productInfo, $requestInfo = null)
    {
        $product = $this->_getProduct($productInfo);
        $productId = $product->getId();
        if ($productId) {
            $request = $this->getQtyRequest($product, $requestInfo);
            try {
                $this->_eventManager->dispatch(
                    'checkout_cart_product_add_before',
                    ['info' => $requestInfo, 'product' => $product]
                );
                $result = $this->getQuote()->addProduct($product, $request);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_checkoutSession->setUseNotice(false);
                $result = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            if (is_string($result)) {
                if ($product->hasOptionsValidationFail()) {
                    $cartQueryParams['startcustomization'] = 1;
                    if (isset($requestInfo['queryID'])) {
                        $cartQueryParams['queryID'] = $requestInfo['queryID'];
                    }
                    $redirectUrl = $product->getUrlModel()->getUrl(
                        $product,
                        ['_query' => $cartQueryParams]
                    );
                } elseif (isset($requestInfo['queryID']) && $requestInfo['queryID'] != '') {
                    $redirectUrl = $product->getUrlModel()->getUrl(
                        $product,
                        ['_query' => ['queryID' => $requestInfo['queryID'] ]]
                    );
                } else {
                    $redirectUrl = $product->getProductUrl();
                }
                $this->_checkoutSession->setRedirectUrl($redirectUrl);
                if ($this->_checkoutSession->getUseNotice() === null) {
                    $this->_checkoutSession->setUseNotice(true);
                }
                throw new \Magento\Framework\Exception\LocalizedException(__($result));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }

        $this->_eventManager->dispatch(
            'checkout_cart_product_add_after',
            ['quote_item' => $result, 'product' => $product]
        );
        $this->_checkoutSession->setLastAddedProductId($productId);
        return $this;
    }

    /**
     * Get request quantity
     *
     * @param Product $product
     * @param \Magento\Framework\DataObject|int|array $request
     * @return int|DataObject
     */
    private function getQtyRequest($product, $request = 0)
    {
        $request = $this->_getProductRequest($request);
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $minimumQty = $stockItem->getMinSaleQty();
        //If product quantity is not specified in request and there is set minimal qty for it
        if ($minimumQty
            && $minimumQty > 0
            && !$request->getQty()
        ) {
            $request->setQty($minimumQty);
        }

        return $request;
    }
}
