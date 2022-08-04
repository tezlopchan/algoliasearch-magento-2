<?php

namespace Algolia\AlgoliaSearch\Model\Source;

use Magento\Config\Model\Config\Backend\File;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\JsonValidator;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use function PHPUnit\Framework\throwException;

class SynonymsFile extends File
{
    /**
     * @var JsonValidator
     */
    protected $jsonValidator;

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param File\RequestData\RequestDataInterface $requestData
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param JsonValidator $jsonValidator
     * @param DriverInterface $driver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        File\RequestData\RequestDataInterface $requestData,
        Filesystem $filesystem,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        JsonValidator $jsonValidator,
        DriverInterface $driver,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $uploaderFactory, $requestData, $filesystem, $resource, $resourceCollection, $data);
        $this->jsonValidator = $jsonValidator;
        $this->driver = $driver;
    }

    protected function _getAllowedExtensions()
    {
        return ['json'];
    }

    public function beforeSave()
    {
        $file = $this->getFileData();
        if (!empty($file)) {
            $data = $this->driver->fileGetContents(($file['tmp_name']));
            if (!$this->jsonValidator->isValid($data)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Json file is not valid. Please check the file')
                );
            }

            $convertJsontoArray = json_decode($data, true);
            foreach ($convertJsontoArray as $jsonArray) {
                if (!array_key_exists('objectID', $jsonArray)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('objectID is missing from the json, please make sure objectId is unique and added for all the synonyms')
                    );
                }
            }
        }
        return parent::beforeSave();
    }
}
