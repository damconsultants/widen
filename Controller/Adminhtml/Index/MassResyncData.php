<?php
namespace DamConsultants\Widen\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use DamConsultants\Widen\Model\ResourceModel\Collection\WidenSycDataCollectionFactory;

class MassResyncData extends Action
{
    /**
     * @var $collectionFactory
     */
    public $collectionFactory;
    /**
     * @var $filter
     */
    public $filter;
    /**
     * Closed constructor.
     *
     * @param Context $context
     * @param \DamConsultants\Widen\Model\BynderSycDataFactory $WidenSycDataFactory
     * @param Filter $filter
     * @param WidenSycDataCollectionFactory $collectionFactory
     * @param \DamConsultants\Widen\Model\WidenSycDataFactory $widenFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        Context $context,
        Filter $filter,
        WidenSycDataCollectionFactory $collectionFactory,
        \DamConsultants\Widen\Model\WidenSycDataFactory $widenFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product\Action $action,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->WidenFactory = $widenFactory;
        $this->_productRepository = $productRepository;
        $this->action = $action;
        $this->storeManagerInterface = $storeManagerInterface;
        parent::__construct($context);
    }
    /**
     * Execute
     *
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $storeId = $this->storeManagerInterface->getStore()->getId();
            $count = 0;
            foreach ($collection as $model) {
                if ($model->getStatus() == 2) {
                    $_product = $this->_productRepository->get($model->getSku());
                    $product_ids[] = $_product->getId();
                    $model = $this->WidenFactory->create()->load($model->getId());
                    $model->setLable('2');
                    $model->setStatus('0');
                    $model->save();
                    $count++;
                }
            }
            $updated_values = [
                'widen_cron_sync' => null
            ];
            $this->action->updateAttributes(
                $product_ids,
                $updated_values,
                $storeId
            );
            $this->messageManager->addSuccess(__('A total of %1 data(s) have been Re-Sync.', $count));
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
        return $this->_authorization->isAllowed('DamConsultants_Widen::resync');
    }
}
