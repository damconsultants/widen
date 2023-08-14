<?php

/**
 * DamConsultants
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  DamConsultants
 * @package   DamConsultants_Widen
 *
 */

namespace DamConsultants\Widen\Controller\Adminhtml\Index;

use DamConsultants\Widen\Model\ResourceModel\Collection\MetaPropertyCollectionFactory;

class SyncSku extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * Get Sku.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \DamConsultants\Widen\Helper\Data $helperData
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param MetaPropertyCollectionFactory $metaPropertyCollectionFactory
     * @param \Magento\Catalog\Api\ProductAttributeManagementInterface $productAttributeManagementInterface
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \DamConsultants\Widen\Model\WidenConfigSyncDataFactory $widensycData
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \DamConsultants\Widen\Helper\Data $helperData,
        \Magento\Catalog\Model\Product\Action $action,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        MetaPropertyCollectionFactory $metaPropertyCollectionFactory,
        \Magento\Catalog\Api\ProductAttributeManagementInterface $productAttributeManagementInterface,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \DamConsultants\Widen\Model\WidenConfigSyncDataFactory $widensycData
    ) {
        parent::__construct($context);
        $this->attribute = $attribute;
        $this->collectionFactory = $collectionFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->resultJsonFactory = $jsonFactory;
        $this->productAction = $action;
        $this->_helperData = $helperData;
        $this->_productRepository = $productRepository;
        $this->metaPropertyCollectionFactory = $metaPropertyCollectionFactory;
        $this->productAttributeManagementInterface = $productAttributeManagementInterface;
        $this->_product = $product;
        $this->file = $file;
        $this->driverFile = $driverFile;
        $this->_widensycData = $widensycData;
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

        $extra_details = [];
        $property_id = null;
        $productSku = [];
        $product_sku = $this->getRequest()->getParam('product_sku');
        $select_attribute = $this->getRequest()->getParam('select_attribute');

        $is_widen_cdn = $this->getRequest()->getParam('is_widen_cdn');
        $is_mg_import = $this->getRequest()->getParam('is_magento_import');
        $extra_details = [
            "is_widen_cdn" => $is_widen_cdn,
            "is_mg_import" => $is_mg_import
        ];
        $result = $this->resultJsonFactory->create();
        $productSku = explode(",", $product_sku);
        $collection = $this->metaPropertyCollectionFactory->create();
        $properties_details = [];
        $all_properties_slug = [];
        if (count($collection->getData()) > 0) {
                
            foreach ($collection->getData() as $metacollection) {
                $properties_details[$metacollection['system_slug']] = [
                    "id" => $metacollection['id'],
                    "property_name" => $metacollection['property_name'],
                    "property_id" => $metacollection['property_id'],
                    "widen_property_slug" => $metacollection['widen_property_slug'],
                    "system_slug" => $metacollection['system_slug'],
                    "system_name" => $metacollection['system_name'],
                ];
            }
            $all_properties_slug = array_keys($properties_details);

        } else {
            $result_data = $result->setData(
                ['status' => 0, 'message' => 'Please Select The Metaproperty First.....']
            );
            return $result_data;
        }
        if (strlen($product_sku) > 0) {
            $productSku = explode(",", $product_sku);
            foreach ($productSku as $sku) {
                $get_data =  $this->_helperData->getWidenImageSyncWithProperties($sku, $properties_details);
                $get_data_json_decode = json_decode($get_data, true);
                $fetch_details = $get_data_json_decode['data'];
                if (count($fetch_details) > 0) {
                    try {
                        $this->getDataItem($select_attribute, $fetch_details, $all_properties_slug, $sku, $extra_details);    
                    } catch(Exception $e) {
                        $insert_data = [
                            "sku" => $sku,
                            "message" => $e->getMessage(),
                            "data_type" => "",
                            "lable" => "0"
                        ];
                        $this->getInsertDataTable($insert_data);
                    }
                } else {
                    $insert_data = [
                        "sku" => $sku,
                        "message" => "Something went wrong from API side, Please contact to support team!",
                        "data_type" => "",
                        "lable" => "0"
                    ];
                    $this->getInsertDataTable($insert_data);
                }

            }
            $result_data = $result->setData(['status' => 1, 'message' => 'Data Sync Successfully.Please check Widen Synchronization Log.!']);
            return $result_data;
        } else {
            $result_data = $result->setData(['status' => 0, 'message' => 'Please enter atleast one SKU.']);
            return $result_data;
        }
    }
    /**
     * Is Json
     *
     * @param string $string
     * @return $this
     */
    public function getIsJSON($string)
    {
        return ((json_decode($string)) === null) ? false : true;
    }
    /**
     * Is Json
     *
     * @param array $string
     * @return $this
     */
    public function getInsertDataTable($insert_data)
    {
        $model = $this->_widensycData->create();
        $data_image_data = [
            'sku' => $insert_data['sku'],
            'widen_sync_data' => $insert_data['message'],
            'widen_data_type' => $insert_data['data_type'],
            'lable' => $insert_data['lable']
        ];
        $model->setData($data_image_data);
        $model->save();
    }
    /**
     * Get Data Item
     *
     * @param string $select_attribute
     * @param array $get_data
     * @param array $all_properties_slug
     * @param array $sku
     * @param array $extra_details
     */
    public function getDataItem($select_attribute, $get_data, $all_properties_slug, $sku, $extra_details)
    {
        $extra_values = $extra_details;
        $image_detail = [];
        $all_item_url = [];
        $video_detail = [];
        $type = [];
        $result = $this->resultJsonFactory->create();
        $_product = $this->_productRepository->get($sku);

        $currentStore = $this->storeManagerInterface->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $storeId = $this->storeManagerInterface->getStore()->getId();
        $product_ids = $_product->getId();
        $image_value = $_product->getWidenMultiImg();
        $doc_value = $_product->getWidenDocument();
        if (!empty($get_data)) {
            if ($select_attribute == 'image') {
                if (!empty($image_value)) {
                    $item_old_value = json_decode($image_value, true);
                    if (count($item_old_value) > 0) {
                        foreach ($item_old_value as $img) {
                            /*** Code by Jayendra ******/
                            if ($img['item_type'] == 'image') {
                                $item_img_url = $this->getPerfectVideoUrl($img['item_url']);
                                $all_item_url[] = $item_img_url;
                            }
                            /************************* */
                        }
                        
                        foreach ($get_data as $data_value) {
                            if ($data_value['Type'] == 'image') {
                                $image_url_new = $this->getPerfectVideoUrl($data_value["Image_Url"]);
                                if (!in_array($image_url_new, $all_item_url)) {
                                    $image_detail[] = [
                                        "item_url" => $image_url_new,
                                        "altText" => $data_value['Alt_Text'],
                                        "image_role" => $data_value['image_roles'],
                                        "item_type" => $data_value['Type'],
                                        "thum_url" => $image_url_new,
                                        "selected_template_url" => $image_url_new,
                                        "height" => "",
                                        "width"=> "",
                                        "is_import" => "0"
                                    ];
                                }
                            }
                        }
                        $image =[];
                        if (count($image_detail) > 0) {
                            foreach ($image_detail as $img) {
                                $type[] = $img['item_type'];
                                $image[] = $img['item_url'];
                            }
                        }
                        $image_value_array = implode(',', $image);
                        $flag = $this->getFlag($type);
                    }
                    if (count($image_detail) > 0) {
                        $array_merge = array_merge($item_old_value, $image_detail);
                        $new_value_array = json_encode($array_merge, true);

                        if (isset($extra_details['is_mg_import']) && $extra_values['is_mg_import'] == 1) {
                            $new_value_array = $this->uploadImageToProduct($new_value_array, $product_ids);
                        }
                        
                        if (isset($extra_values['is_widen_cdn']) && $extra_values['is_widen_cdn'] == 1) {
                            $update_details = [
                                'widen_multi_img' => $new_value_array,
                                'use_widen_cdn' => 1
                            ];
                        } else {
                            $update_details = [
                                'widen_multi_img' => $new_value_array,
                                'use_widen_cdn' => 0
                            ];
                        }
                        $data_image_data = [
                            'sku' => $sku,
                            'message' => $image_value_array,
                            'data_type' => '1',
                            "lable" => "1"
                        ];
                        $this->getInsertDataTable($data_image_data);
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            $update_details,
                            $storeId
                        );
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            ['widen_isMain' => $flag],
                            $storeId
                        );
                    } else {
                        if (isset($extra_values['is_widen_cdn']) && $extra_values['is_widen_cdn'] == 1) {
                            $update_details = [
                                'use_widen_cdn' => 1
                            ];
                        } else {
                            $update_details = [
                                'use_widen_cdn' => 0
                            ];
                        }
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            $update_details,
                            $storeId
                        );
                        $data_image_data = [
                            'sku' => $sku,
                            'message' => "Don't Have Find New Data For this SKU",
                            'data_type' => '',
                            "lable" => ""
                        ];
                        $this->getInsertDataTable($data_image_data);
                    }
                } else {
                    foreach ($get_data as $data_value) {
                        if ($data_value['Type'] == 'image') {
                            $image_url_new = $this->getPerfectVideoUrl($data_value["Image_Url"]);
                            $image_detail[] = [
                                "item_url" => $image_url_new,
                                "altText" => $data_value['Alt_Text'],
                                "image_role" => $data_value['image_roles'],
                                "item_type" => $data_value['Type'],
                                "thum_url" => $image_url_new,
                                "selected_template_url" => $image_url_new,
                                "height" => "",
                                "width"=> "",
                                "is_import" => "0"
                            ];
                        }
                    }
                    foreach ($image_detail as $img) {
                        $type[] = $img['item_type'];
                        $image[] = $img['item_url'];
                    }
                    $image_value_array = implode(',', $image);
                    $flag = $this->getFlag($type);
                    $new_value_array = json_encode($image_detail, true);

                    if (isset($extra_details['is_mg_import']) && $extra_values['is_mg_import'] == 1) {
                        $new_value_array = $this->uploadImageToProduct($new_value_array, $product_ids);
                    }

                    if (isset($extra_values['is_widen_cdn']) && $extra_values['is_widen_cdn'] == true) {
                        $update_details = [
                            'widen_multi_img' => $new_value_array,
                            'use_widen_cdn' => 1
                        ];
                    } else {
                        $update_details = [
                            'widen_multi_img' => $new_value_array
                        ];
                    }
                    $data_image_data = [
                        'sku' => $sku,
                        'message' => $image_value_array,
                        'data_type' => '1',
                        "lable" => "1"
                    ];
                    $this->getInsertDataTable($data_image_data);
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        $update_details,
                        $storeId
                    );
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        ['widen_isMain' => $flag],
                        $storeId
                    );
                }
            } elseif ($select_attribute == "video") {
                if (!empty($image_value)) {
                    $item_old_value = json_decode($image_value, true);
                    if (count($item_old_value) > 0) {
                        foreach ($item_old_value as $video) {
                            $vide_url = $this->getPerfectVideoUrl($video['item_url']);
                            $all_item_url[] = $vide_url;
                        }
                        
                        foreach ($get_data as $data_value) {
                            if ($data_value['Type'] == 'video') {
                                $data_img_url = $this->getPerfectVideoUrl($data_value["Image_Url"]);
                                if (!in_array($data_img_url, $all_item_url)) {
                                    $img_array = $this->dataHelper->getMakeVideoasThumbForSync($data_value, $sku, $mediaUrl);
                                    $video_detail[] = [
                                        "item_url" => $data_img_url,
                                        "altText" => !empty($data_value['Alt_Text'])?$data_value['Alt_Text']:"",
                                        "image_role" => null,
                                        "item_type" => $data_value['Type'],
                                        "thum_url" => $data_img_url,
                                        "selected_template_url" => $img_array['template_url'],
                                        "height" => "",
                                        "width"=> "",
                                        "is_import" => "0"
                                    ];
                                }
                            }
                        }
                        if (count($video_detail) > 0) {
                            foreach ($video_detail as $video) {
                                $type[] = $video['item_type'];
                            }
                        }
                        $flag = $this->getFlag($type);
                    }
                    if (count($video_detail) > 0) {
                        
                        $array_merge = array_merge($item_old_value, $video_detail);
                        $new_value_array = json_encode($array_merge, true);

                        if (isset($extra_values['is_widen_cdn']) && $extra_values['is_widen_cdn'] == true) {
                            $update_details = [
                                'widen_multi_img' => $new_value_array,
                                'use_widen_cdn' => 1
                            ];
                        } else {
                            $update_details = [
                                'widen_multi_img' => $new_value_array
                            ];
                        }
                        $data_video_data = [
                            'sku' => $product_sku_key,
                            'message' => $new_value_array,
                            'data_type' => '3',
                            "lable" => "1"
                        ];
                        $this->getInsertDataTable($data_video_data);
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            $update_details,
                            $storeId
                        );
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            ['widen_isMain' => $flag],
                            $storeId
                        );
                    }
                } else {
                    foreach ($get_data as $data_value) {
                        if ($data_value['Type'] == 'video') {
                            $data_img_url = $this->getPerfectVideoUrl($data_value["Image_Url"]);
                            $video_detail[] = [
                                "item_url" => $data_img_url,
                                "altText" => !empty($data_value['Alt_Text'])?$data_value['Alt_Text']:"",
                                "image_role" => null,
                                "item_type" => $data_value['Type'],
                                "thum_url" => $data_img_url,
                                "selected_template_url" => $data_img_url,
                                "height" => "",
                                "width"=> "",
                                "is_import" => "0"
                            ];
                        }
                    }
                    foreach ($video_detail as $video) {
                        $type[] = $video['item_type'];
                    }
                    $flag = $this->getFlag($type);
                    $new_value_array = json_encode($video_detail, true);

                    if (isset($extra_values['is_widen_cdn']) && $extra_values['is_widen_cdn'] == true) {
                        $update_details = [
                            'widen_multi_img' => $new_value_array,
                            'use_widen_cdn' => 1
                        ];
                    } else {
                        $update_details = [
                            'widen_multi_img' => $new_value_array
                        ];
                    }
                    $data_video_data = [
                        'sku' => $product_sku_key,
                        'message' => $new_value_array,
                        'data_type' => '3',
                        "lable" => "1"
                    ];
                    $this->getInsertDataTable($data_video_data);
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        $update_details,
                        $storeId
                    );
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        ['widen_isMain' => $flag],
                        $storeId
                    );
                    
                }
            } else {
                if (empty($doc_value)) {
                    $doc_detail=[];
                    foreach ($get_data as $data_value) {
                        if ($data_value['Type'] == 'pdf' || $data_value['Type'] == 'office') {
                            $data_doc_url = $this->getPerfectVideoUrl($data_value["Image_Url"]);
                                $doc_detail[] = [
                                    "item_url" => $data_doc_url,
                                    "item_type" => $data_value['Type'],
                                    "altText" => $data_value['Alt_Text'],
                                    "doc_name" => $data_value['Alt_Text'],
                                ];
                        }
                    }
                    $new_value_array = json_encode($doc_detail, true);
                    $data_doc_data = [
                        'sku' => $product_sku_key,
                        'message' => $new_value_array,
                        'data_type' => '2',
                        "lable" => "1"
                    ];
                    $this->getInsertDataTable($data_doc_data);
                    $this->productAction->updateAttributes(
                        [$product_ids],
                        ['widen_document' => $new_value_array],
                        $storeId
                    );
                } else {
                    /**Not empty means need to add all new Documents */
                    $old_value = json_decode($doc_value, true);
                    $doc_detail = [];
                    $existing_urls = [];
                    foreach ($old_value as $existing_doc_val) {
                        $existing_urls[] = $existing_doc_val["item_url"];
                    }

                    foreach ($get_data as $all_new_urls) {
                        if ($all_new_urls["Type"] == "pdf" || $all_new_urls["Type"] == "office") {
                            $new_link = $this->getPerfectVideoUrl($all_new_urls['Image_Url']);
                            if (!in_array($new_link, $existing_urls)) {
                                $doc_detail[] = [
                                    "item_url" => $new_link,
                                    "item_type" => $all_new_urls['Type'],
                                    "altText" => $all_new_urls['Alt_Text'],
                                    "doc_name" => $all_new_urls['Alt_Text'],
                                ];
                            }
                        }
                    }
                    if (count($doc_detail) > 0) {
                        $array_merge = array_merge($old_value, $doc_detail);
                        $new_value_array = json_encode($array_merge, true);
                        $data_doc_data = [
                            'sku' => $product_sku_key,
                            'message' => $new_value_array,
                            'data_type' => '2',
                            "lable" => "1"
                        ];
                        $this->getInsertDataTable($data_doc_data);
                        $this->productAction->updateAttributes(
                            [$product_ids],
                            ['widen_document' => $new_value_array],
                            $storeId
                        );
                    }
                }
            }
        }
    }
    /**
     * Get Flag
     *
     * @param array $type
     */
    public function getFlag($type)
    {
        $flag = 0;
        if (in_array("image", $type) && in_array("video", $type)) {
            $flag = 1;
        } elseif (in_array("image", $type)) {
            $flag = 2;
        } elseif (in_array("video", $type)) {
            $flag = 3;
        }
        return $flag;
    }

    /**
     * Get perfect video url
     *
     * @param string $url
     */
    public function getPerfectVideoUrl($url)
    {
        $new_url = $url;
        $query_params = [];
        if (strlen(trim($url)) > 0) {
            $query_str = parse_url($url, PHP_URL_QUERY);
            if ($query_str != null) {
                parse_str($query_str, $query_params);
                if (isset($query_params['download'])) {
                    $new_url = str_replace("&download=true", "", $url);
                }
            }
        }
        return $new_url;
    }

    /**
     * Upload image into product
     *
     * @param string upload_details
     * @param string $id
     */
    public function uploadImageToProduct($upload_details, $id)
    {
        $product = $this->_product->load($id);
        $new_json_decode = json_decode($upload_details, true);
        $result = $this->resultJsonFactory->create();
        $dir_path = "Acquia_DAM_temp/";
        $img_dir = BP . '/pub/media/wysiwyg/' . $dir_path;
        if (!$this->file->fileExists($img_dir)) {
            $this->file->mkdir($img_dir, 0755, true);
        }
        foreach ($new_json_decode as $k => $item) {
            
            if ($item['item_type'] == 'image' && $item["is_import"] == "0") {
                $item_url = trim($item['item_url']);
                if (!empty($item_url)) {
                    $fileInfo = $this->file->getPathInfo($item_url);
                    $basename = $fileInfo['basename'];
                    $file_name = explode("?", $basename);
                    $file_name = $file_name[0];
                    $file_name = str_replace("%20", " ", $file_name);
                    $img_url = $img_dir . $file_name;
                    $this->file->write(
                        $img_url,
                        $this->driverFile->fileGetContents($item_url)
                    );
                    $product->addImageToMediaGallery($img_url, $item['image_role'], false, false);
                    $img_label = $item["altText"];
                    if ($item["altText"] != "") {
                        $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                        foreach ($existingMediaGalleryEntries as $key => $entry) {
                            if (empty($entry['label'])) {
                                $entry->setLabel($img_label);
                            }
                        }
                        $product->setStoreId(0);
                        $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                    }
                    $product->save();
                    $result_data = $result->setData([
                        'status' => 1,
                        'message' => 'Image Import in Folder Successfully..!'
                    ]);
                    unlink($img_url);
                    $new_json_decode[$k]["is_import"] = "1";
                }
            }
        }
        return json_encode($new_json_decode, true);
    }
}
