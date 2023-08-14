<?php
/**
 * DamConsultants
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ecomteck.com license that is
 * available through the world-wide-web at this URL:
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    DamConsultants
 * @package     DamConsultants_Widen
 */
namespace DamConsultants\Widen\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    public function toOptionArray()
    {
        
        return [
            [
                'value' => 0,
                'label' => __('Error'),
            ],
            [
                'value' => 1,
                'label' => __('Success'),
            ],
            [
                'value' => 2,
                'label' => __('Re Sync'),
            ],
        ];
    }
}
