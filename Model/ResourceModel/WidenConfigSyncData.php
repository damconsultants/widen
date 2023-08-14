<?php

namespace DamConsultants\Widen\Model\ResourceModel;

class WidenConfigSyncData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Widen Syc Data
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init('widen_config_sync_data', 'id');
    }
}