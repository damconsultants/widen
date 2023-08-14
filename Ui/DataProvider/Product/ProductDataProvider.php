<?php

namespace DamConsultants\Widen\Ui\DataProvider\Product;

use DamConsultants\Widen\Model\ResourceModel\Collection\WidenSycDataCollectionFactory;

class ProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @param WidenSycDataCollectionFactory $WidenSycDataCollectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        WidenSycDataCollectionFactory $WidenSycDataCollectionFactory,
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
