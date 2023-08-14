<?php

namespace DamConsultants\Widen\Model\ResourceModel\Collection;

class MetaPropertyCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * MetaPropertyCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Widen\Model\MetaProperty::class,
            \DamConsultants\Widen\Model\ResourceModel\MetaProperty::class
        );
    }
}
