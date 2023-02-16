<?php

namespace Algolia\AlgoliaSearch\Block\Adminhtml\Category;

use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Algolia\AlgoliaSearch\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;

class Merchandising extends \Magento\Backend\Block\Template
{
    /** @var string */
    protected $_template = 'catalog/category/edit/merchandising.phtml';

    /** @var Registry */
    protected $registry;

    /** @var ConfigHelper */
    private $configHelper;

    /** @var Data */
    private $coreHelper;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ConfigHelper $configHelper
     * @param Data $coreHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ConfigHelper $configHelper,
        Data $coreHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->configHelper = $configHelper;
        $this->coreHelper = $coreHelper;
        $this->storeManager = $context->getStoreManager();

        parent::__construct($context, $data);
    }

    /** @return Category | null */
    public function getCategory()
    {
        return $this->registry->registry('category');
    }

    /** @return bool */
    public function isRootCategory()
    {
        $category = $this->getCategory();

        if ($category) {
            $path = $category->getPath();
            if (!$path) {
                return false;
            }
            $parts = explode('/', $path);
            if (count($parts) <= 2) {
                return true;
            }
        }

        return false;
    }

    /** @return ConfigHelper */
    public function getConfigHelper()
    {
        return $this->configHelper;
    }

    /** @return Data */
    public function getCoreHelper()
    {
        return $this->coreHelper;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return \Magento\Store\Api\Data\StoreInterface|null
     */
    public function getCurrentStore()
    {
        if ($storeId = $this->getRequest()->getParam('store')) {
            return $this->storeManager->getStore($storeId);
        }

        return $this->storeManager->getDefaultStoreView();
    }

    /**
     * @return string
     */
    public function getPageModeOnly()
    {
        return Category::DM_PAGE;
    }

    /**
     * @return bool
     */
    public function canDisplayProducts()
    {
        if ($this->getCategory()->getDisplayMode() == $this->getPageModeOnly()) {
            return false;
        }

        return true;
    }
}
