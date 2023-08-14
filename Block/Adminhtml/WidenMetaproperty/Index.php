<?php

namespace DamConsultants\Widen\Block\Adminhtml\WidenMetaproperty;

use DamConsultants\Widen\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;
use DamConsultants\Widen\Model\ResourceModel\Collection\DefaultMetaPropertyCollectionFactory;

class Index extends \Magento\Backend\Block\Template
{
    /**
     * @var \DamConsultants\Widen\Helper\Data
     */
    protected $helperdata;

    /**
     * @var \DamConsultants\Widen\Model\MetaPropertyFactory
     */
    protected $metaProperty;

    /**
     * @var \DamConsultants\Widen\Model\ResourceModel\Collection\MetaPropertyCollectionFactory
     */
    protected $metaPropertyCollectionFactory;

    /**
     * Metaproperty
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \DamConsultants\Widen\Helper\Data $helperdata
     * @param \DamConsultants\Widen\Model\MetaPropertyFactory $metaProperty
     * @param MetaPropertyCollectionFactory $metaPropertyCollectionFactory
     * @param DefaultMetaPropertyCollectionFactory $DefaultMetaPropertyCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \DamConsultants\Widen\Helper\Data $helperdata,
        \DamConsultants\Widen\Model\MetaPropertyFactory $metaProperty,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory,
        DefaultMetaPropertyCollectionFactory $DefaultMetaPropertyCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_helperdata = $helperdata;
        $this->_metaProperty = $metaProperty;
        $this->_metaPropertyCollectionFactory = $metaPropertyCollectionFactory;
        $this->_default_metaProperty_collection = $DefaultMetaPropertyCollectionFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * SubmitUrl.
     *
     * @return $this
     */
    public function getSubmitUrl()
    {
        return $this->getUrl("acquiadam/index/submit");
    }

    /**
     * Get MetaData.
     *
     * @return array
     */
    public function getMetaData()
    {
        $response_data = [];
        $attribute_array = [];
        $defaultmetaPropertycollection = $this->_default_metaProperty_collection->create();
        $defaultmetaPropertycollection_data = $defaultmetaPropertycollection->getData();
        if (count($defaultmetaPropertycollection_data) > 0) {
            foreach ($defaultmetaPropertycollection_data as $meta_val) {
                $attribute_array[$meta_val['widen_property_slug']] = $meta_val['property_name'];
            }
        }
        $collection = $this->_metaPropertyCollectionFactory->create();
        if (count($attribute_array) > 0) {
            $response_data['metadata'] = $attribute_array;
        } else {
            $response_data['metadata'] = [];
        }
        $properties_details = [];
        $collection_data_array = $collection->getData();
        if (count($collection_data_array) > 0) {
            foreach ($collection_data_array as $metacollection) {
                $properties_details[$metacollection['system_slug']] = [
                    "id" => $metacollection['id'],
                    "property_name" => $metacollection['property_name'],
                    "property_id" => $metacollection['property_id'],
                    "widen_property_slug" => $metacollection['widen_property_slug'],
                    "system_slug" => $metacollection['system_slug'],
                    "system_name" => $metacollection['system_name'],
                ];
            }
            $response_data['sku_selected'] = $properties_details["sku"]["widen_property_slug"];
            $response_data['image_role_selected'] = $properties_details["image_role"]["widen_property_slug"];
            $response_data['image_alt_text'] = $properties_details["alt_text"]["widen_property_slug"];
        } else {
            $response_data['sku_selected'] = '0';
            $response_data['image_role_selected'] = '0';
            $response_data['image_alt_text'] = '0';
        }
        return $response_data;
    }
    /**
     * Get main url.
     *
     * @return string
     */
    public function getMainUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}
