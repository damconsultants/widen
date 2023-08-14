<?php
namespace DamConsultants\Widen\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class DeleteCronSyncData extends Action
{
    public $BynderConfigSyncDataFactory;
    /**
     * Closed constructor.
     *
     * @param Context $context
     * @param DamConsultants\Widen\Model\WidenSycDataFactory $WidenSycDataFactory
     */
    public function __construct(
        Context $context,
        \DamConsultants\Widen\Model\WidenSycDataFactory $WidenSycDataFactory
    ) {
        $this->widenSycDataFactory = $WidenSycDataFactory;
        parent::__construct($context);
    }
    /**
     * Execute
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        try {
            $syncModel = $this->widenSycDataFactory->create();
            $syncModel->load($id);
            $syncModel->delete();
            $this->messageManager->addSuccessMessage(__('You deleted the sync data.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('acquiadam/index/acquiadamgrid');
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('DamConsultants_Widen::delete');
    }
}
