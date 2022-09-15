<?php

namespace Algolia\AlgoliaSearch\Block\Widget;

use Magento\Framework\Math\Random;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;

class TrendsItem extends Template implements BlockInterface
{
    protected $_template = 'recommend/widget/trends-item.phtml';
    
    /**
     * @param Context $context
     * @param Random $mathRandom
     * @param array $data
     */
    public function __construct(
        Context $context,
        Random $mathRandom,
        array $data = []
    ) {
        $this->mathRandom = $mathRandom;
        parent::__construct(
            $context,
            $data
        );
    }
 
    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateUniqueToken()
    {
        return $this->mathRandom->getRandomString(5);
    }
}
