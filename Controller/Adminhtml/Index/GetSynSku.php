<?php

namespace DamConsultants\Widen\Controller\Adminhtml\Index;

class GetSynSku extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * Get Sku.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Catalog\Api\ProductAttributeManagementInterface $productAttributeManagementInterface
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Catalog\Api\ProductAttributeManagementInterface $productAttributeManagementInterface,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->attribute = $attribute;
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory = $jsonFactory;
        $this->productAttributeManagementInterface = $productAttributeManagementInterface;
    }
    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {

        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
            return;
        }

        $attribute_value = $this->getRequest()->getParam('select_attribute');
        $sku_limit = $this->getRequest()->getParam('sku_limit');

        $product_sku = [];
        $sku = [];
        $id = [];
        $attribute = $this->collectionFactory->create();
        $productcollection = $this->collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        if ($sku_limit != 0) {
            $productcollection->getSelect()->limit($sku_limit);
        }
        if (count($attribute) > 0) {
            foreach ($attribute as $value) {
                $id[] = $value['attribute_set_id'];
            }
        }
        $array = array_unique($id);
        if (count($array) > 0) {
            foreach ($array as $ids) {
                $productAttributes = $this->productAttributeManagementInterface->getAttributes($ids);
                foreach ($productAttributes as $atttr) {
    
                    if ($atttr->getAttributeCode() == "widen_multi_img") {
                        $image_id[] = $atttr->getAttributeSetId();
                    } elseif ($atttr->getAttributeCode() == "widen_document") {
    
                        $doc_id[] = $atttr->getAttributeSetId();
                    }
                }
            }
        }
        $final = array_merge($image_id, $doc_id);
        $ids = array_unique($final);
        if (!empty($attribute_value)) {
            if ($attribute_value == "image") {
                $productcollection->addAttributeToFilter('attribute_set_id', $image_id);
                
                foreach ($productcollection as $product) {
                    if (!empty($product['widen_multi_img'])) {
                        if ($product['widen_isMain'] != "2" && $product['widen_isMain'] != "1") {
                            $product_sku[] = $product->getSku();
                        }
                    } else {
                        $product_sku[] = $product->getSku();
                    }
                }
            } elseif ($attribute_value == "video") {
                $productcollection->addAttributeToFilter('attribute_set_id', $image_id);
                
                foreach ($productcollection as $product) {
                    if (!empty($product['widen_multi_img'])) {
                        if ($product['widen_isMain'] != "3" && $product['widen_isMain'] != "1") {
                            $product_sku[] = $product->getSku();
                        }
                    } else {
                        $product_sku[] = $product->getSku();
                    }
                    
                }
            } elseif ($attribute_value == "document") {

                $productcollection->addAttributeToFilter('attribute_set_id', $doc_id)
                    ->addAttributeToFilter(
                        [
                            ['attribute' => 'widen_document', 'null' => true]
                        ]
                    );
                foreach ($productcollection as $product) {
                    $product_sku[] = $product->getSku();
                }
            }
        } else {

            $productcollection->addAttributeToFilter('attribute_set_id', $ids)
                ->addAttributeToFilter(
                    [
                        ['attribute' => 'widen_multi_img', 'null' => true],
                        ['attribute' => 'widen_document', 'null' => true]
                    ]
                );
            foreach ($productcollection as $product) {
                $product_sku[] = $product->getSku();
            }
        }
        $sku = array_unique($product_sku);
       
        if (count($sku) > 0) {
            $status = 1;
            $data_sku = $sku;
        } else {
            $status = 0;
            $data_sku = "There is not any empty Widen Data in product";
        }
        $result = $this->resultJsonFactory->create();
        return $result->setData(['status' => $status, 'message' => $data_sku]);
    }
}
