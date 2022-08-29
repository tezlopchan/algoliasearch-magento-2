<?php
namespace Algolia\AlgoliaSearch\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Math\Random;

class TrendsItem extends Template implements BlockInterface {
    protected $_template = "recommend/widget/trends-item.phtml";

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

    public function generateUniqueToken()
    {
        return $this->mathRandom->getRandomString(5);
    }

}
