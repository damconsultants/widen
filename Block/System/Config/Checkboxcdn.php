<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace DamConsultants\Widen\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;

class Checkboxcdn extends Field
{
    /**
     * @var const
     */
    public const CONFIG_PATH = 'widensyncdata/widen_sync_data/check_cdn';
    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'DamConsultants_Widen::system/config/checkboxcdn.phtml';
    /**
     * Null Variable
     *
     * @var null
     */
    protected $_values = null;
    /**
     * Retrieve element HTML markup.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setNamePrefix($element->getName())
            ->setHtmlId($element->getHtmlId());
        return $this->_toHtml();
    }
    
    /**
     * Getvalue.
     *
     * @return $this
     */
    public function getValues()
    {
        $values = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $val = $objectManager->create(\DamConsultants\Widen\Model\Config\Source\Checkboxcdn::class)->toOptionArray();
        foreach ($val as $value) {
            $values[$value['value']] = $value['label'];
        }
        return $values;
    }
    /**
     * Get Is Check
     *
     * @param string $name
     * @return boolean
     */
    public function getIsChecked($name)
    {
        return in_array($name, $this->getCheckedValues());
    }
    /**
     * Get the checked value from config
     */
    public function getCheckedValues()
    {
        if ($this->_values == null) {
            $data = $this->getConfigData();
            if (isset($data[self::CONFIG_PATH])) {
                $data = $data[self::CONFIG_PATH];
            } else {
                $data = '';
            }
            $this->_values = explode(',', $data);
        }
        return $this->_values;
    }
    /**
     * GetNPrefix.
     *
     * @return $this
     */
    public function getNPrefix()
    {
        return $this->getNamePrefix();
    }

    /**
     * GetId.
     *
     * @return $this
     */
    public function getId()
    {
        return $this->getHtmlId();
    }

    /**
     * GetCheck.
     *
     * @return $this
     * @param string $name
     */
    public function getCheck($name)
    {
        return $this->getIsChecked($name);
    }
}
