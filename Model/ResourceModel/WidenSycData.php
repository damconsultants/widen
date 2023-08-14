<?php

namespace DamConsultants\Widen\Model\ResourceModel;

class WidenSycData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('widen_cron_data', 'id');
    }
}
