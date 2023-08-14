<?php
namespace DamConsultants\Widen\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use DamConsultants\Widen\Model\ResourceModel\Collection\WidenSycDataCollectionFactory;

class MassDeleteCronSyncData extends Action
{
    /**
     * @var $collectionFactory
     */
    public $collectionFactory;
    /**
     * @var Filter
     */
    public $filter;
    /**
     * Closed constructor.
     *
     * @param Context $context
     * @param \DamConsultants\Widen\Model\BynderSycDataFactory $WidenSycDataFactory
     * @param Filter $filter
     * @param WidenSycDataCollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        WidenSycDataCollectionFactory $collectionFactory,
        \DamConsultants\Widen\Model\WidenSycDataFactory $widenFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->WidenFactory = $widenFactory;
        parent::__construct($context);
    }
    /**
     * Execute
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $count = 0;
            foreach ($collection as $model) {
                $model = $this->WidenFactory->create()->load($model->getId());
                $model->delete();
                $count++;
            }
            $this->messageManager->addSuccess(__('A total of %1 data(s) have been deleted.', $count));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('acquiadam/index/acquiadamgrid');
    }
    /**
     * Is Allowed
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('DamConsultants_Widen::delete');
    }
}
