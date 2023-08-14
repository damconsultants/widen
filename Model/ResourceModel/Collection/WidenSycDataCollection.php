<?php

namespace DamConsultants\Widen\Model\ResourceModel\Collection;

class WidenSycDataCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Widen\Model\WidenSycData::class,
            \DamConsultants\Widen\Model\ResourceModel\WidenSycData::class
        );
    }
}
