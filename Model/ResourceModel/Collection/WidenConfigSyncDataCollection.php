<?php

namespace DamConsultants\Widen\Model\ResourceModel\Collection;

class WidenConfigSyncDataCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * Widen ConfigSyncDataCollection
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(
            \DamConsultants\Widen\Model\WidenConfigSyncData::class,
            \DamConsultants\Widen\Model\ResourceModel\WidenConfigSyncData::class
        );
    }
}
