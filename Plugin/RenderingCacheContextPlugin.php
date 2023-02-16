<?php

namespace Algolia\AlgoliaSearch\Plugin;

use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;

/**
 * The purpose of this class is to render different cached versions of the pages according to the user agent.
 * If the "prevent backend rendering" configuration is turned on, we don't want to render the results on the backend
 * side only for humans but we want to do it for the robots (configured in the "advanced" section of the extension).
 * So with this plugin, two versions of the pages are cached : one for humans, and one for robots.
 */
class RenderingCacheContextPlugin
{
    public const RENDERING_CONTEXT = 'rendering_context';
    public const RENDERING_WITH_BACKEND = 'with_backend';
    public const RENDERING_WITHOUT_BACKEND = 'without_backend';

    private $configHelper;
    private $storeManager;
    private $request;

    public function __construct(
        ConfigHelper $configHelper,
        StoreManagerInterface $storeManager,
        Http $request
    ) {
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * Add Rendering context for caching purposes
     * (If the prevent rendering configuration is enabled and the user agent has no white card to display it,
     * we set a different page variation, and the FPC stores a different cached page)
     *
     * @param HttpContext $subject
     * @param string[] $data
     *
     * @return array
     */
    public function afterGetData(HttpContext $subject, $data)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!($this->request->getControllerName() === 'category'
            && $this->configHelper->replaceCategories($storeId) === true)) {
            return $data;
        }

        $context = $this->configHelper->preventBackendRendering() ?
            self::RENDERING_WITHOUT_BACKEND :
            self::RENDERING_WITH_BACKEND;

        $data[self::RENDERING_CONTEXT] = $context;

        return $data;
    }
}
