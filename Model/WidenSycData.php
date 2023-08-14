<?php

namespace DamConsultants\Widen\Model;

class WidenSycData extends \Magento\Framework\Model\AbstractModel
{
    public const CACHE_TAG = 'DamConsultants_Widen';

    protected $_cacheTag = 'DamConsultants_Widen';

    protected $_eventPrefix = 'DamConsultants_Widen';

    protected function _construct()
    {
        $this->_init(\DamConsultants\Widen\Model\ResourceModel\WidenSycData::class);
    }
}
