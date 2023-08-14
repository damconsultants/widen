<?php
namespace DamConsultants\Widen\Block\Adminhtml\Catalog\Product\Form;

class WidenDoc extends \Magento\Backend\Block\Template
{
    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'group/widendoc.phtml';
    /**
     * Gallery
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Helper\Data $helperdata
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Helper\Data $helperdata
    ) {
        $this->_registry = $registry;
        $this->_helperData = $helperdata;
        parent::__construct($context);
    }
    /**
     * Get Backend Name
     *
     * @return $this
     */
    public function getBackendArea()
    {
        return $this->_helperData->getAreaFrontName();
    }
    /**
     * Get Image Roll
     *
     * @return $this
     * @param string $currentProduct
     */
    public function getProduct($currentProduct)
    {
        return $this->_registry->registry($currentProduct);
    }
    /**
     * EntityId.
     *
     * @return $this
     */
    public function getEntityId()
    {
        return $this->getRequest()->getParam('id');
    }
    /**
     * Image.
     *
     * @return $this
     */
    public function getDrag()
    {
        return $this->getViewFileUrl('DamConsultants_Widen::images/drag.png');
    }
    /**
     * Image.
     *
     * @return $this
     */
    public function getDelete()
    {
        return $this->getViewFileUrl('DamConsultants_Widen::images/delete_.avif');
    }

    /**
     * Image.
     *
     * @return $this
     */
    public function getPreloader()
    {
        return $this->getViewFileUrl('DamConsultants_Widen::images/loader_new.gif');
    }
}
