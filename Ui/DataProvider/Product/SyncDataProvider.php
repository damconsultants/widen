<?php

namespace DamConsultants\Widen\Ui\DataProvider\Product;

use DamConsultants\Widen\Model\ResourceModel\Collection\WidenConfigSyncDataCollectionFactory;

class SyncDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @param WidenConfigSyncDataCollectionFactory $WidenSycDataCollectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        WidenConfigSyncDataCollectionFactory $WidenSycDataCollectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $collection = $WidenSycDataCollectionFactory;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        return $this->collection = $WidenSycDataCollectionFactory->create();
    }
}
