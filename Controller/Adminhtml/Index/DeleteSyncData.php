<?php
namespace DamConsultants\Widen\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class DeleteSyncData extends Action
{
    public $WidenConfigSyncDataFactory;
    /**
     * Closed constructor.
     *
     * @param Context $context
     * @param DamConsultants\Widen\Model\WidenConfigSyncDataFactory $WidenConfigSyncDataFactory
     */
    public function __construct(
        Context $context,
        \DamConsultants\Widen\Model\WidenConfigSyncDataFactory $WidneConfigSyncDataFactory
    ) {
        $this->widenConfigSyncDataFactory = $WidenConfigSyncDataFactory;
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
            $syncModel = $this->widenConfigSyncDataFactory->create();
            $syncModel->load($id);
            $syncModel->delete();
            $this->messageManager->addSuccessMessage(__('You deleted the sync data.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('acquiadam/index/sync');
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('DamConsultants_Widen::delete');
    }
}
