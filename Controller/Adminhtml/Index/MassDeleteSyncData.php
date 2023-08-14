<?php
namespace DamConsultants\Widen\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use DamConsultants\Widen\Model\ResourceModel\Collection\WidenConfigSyncDataCollectionFactory;

class MassDeleteSyncData extends Action
{
    public $collectionFactory;

    public $filter;

    public function __construct(
        Context $context,
        Filter $filter,
        WidenConfigSyncDataCollectionFactory $collectionFactory,
        \DamConsultants\Widen\Model\WidenConfigSyncDataFactory $widenFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->widenFactory = $widenFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());

            $count = 0;
            foreach ($collection as $model) {
                $model = $this->widenFactory->create()->load($model->getId());
                $model->delete();
                $count++;
            }
            $this->messageManager->addSuccess(__('A total of %1 data(s) have been deleted.', $count));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('acquiadam/index/sync');
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('DamConsultants_Widen::delete');
    }
}
