<?php
namespace DamConsultants\Widen\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class ReSyncData extends Action
{
    /**
     * Closed constructor.
     *
     * @param Context $context
     * @param \DamConsultants\Widen\Model\BynderSycDataFactory $WidenSycDataFactory
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        Context $context,
        \DamConsultants\Widen\Model\WidenSycDataFactory $WidenSycDataFactory,
        \Magento\Catalog\Model\Product\Action $action,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface

    ) {
        $this->widenSycDataFactory = $WidenSycDataFactory;
        $this->_productRepository = $productRepository;
        $this->action = $action;
        $this->storeManagerInterface = $storeManagerInterface;
        parent::__construct($context);
    }
    /**
     * Execute
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        $storeId = $this->storeManagerInterface->getStore()->getId();
        try {
            $syncModel = $this->widenSycDataFactory->create();
            $syncModel->load($id);
            $sku = $syncModel->getSku();
            $updated_values = [
                'widen_cron_sync' => null
            ];
            $_product = $this->_productRepository->get($sku);
            $product_ids = $_product->getId();

            $this->action->updateAttributes(
                [$product_ids],
                $updated_values,
                $storeId
            );
            $syncModel->setLable('2');
            $syncModel->setStatus('0');
            $syncModel->save();
            $this->messageManager->addSuccessMessage(__('SKU ('. $sku.') will re-sync again.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('acquiadam/index/acquiadamgrid');
    }
    /**
     * Is Allowed
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('DamConsultants_Widen::resync');
    }
}
