<?php

namespace DamConsultants\Widen\Model;

class WidenConfigSyncData extends \Magento\Framework\Model\AbstractModel
{
    protected const CACHE_TAG = 'DamConsultants_Widen';

    /**
     * @var $_cacheTag
     */
    protected $_cacheTag = 'DamConsultants_Widen';

    /**
     * @var $_eventPrefix
     */
    protected $_eventPrefix = 'DamConsultants_Widen';

    /**
     * Widen Syc Data
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_init(\DamConsultants\Widen\Model\ResourceModel\WidenConfigSyncData::class);
    }
}