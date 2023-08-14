<?php

namespace DamConsultants\Widen\Model\ResourceModel\Collection;

class DefaultMetaPropertyCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * MetaPropertyCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Widen\Model\DefaultMetaProperty::class,
            \DamConsultants\Widen\Model\ResourceModel\DefaultMetaProperty::class
        );
    }
}
