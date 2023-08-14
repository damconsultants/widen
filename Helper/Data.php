<?php

namespace DamConsultants\Widen\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\Bulk;

class Data extends AbstractHelper
{
    /**
     * @var $storeScope
     */
    protected $storeScope;
    /**
     * @var $productrepository
     */
    protected $productrepository;
    /**
     * @var $filesystem
     */
    protected $filesystem;
    /**
     * @var $_scopeConfig
     */
    protected $_scopeConfig;
    /**
     * @var $by_redirecturl
     */
    public $by_redirecturl;
    /**
     * @var $permanent_token
     */
    public $permanent_token = "";

    public const PERMANENT_TOKEN = 'widenconfig/basic_credential/widen_permanent_token';
    public const WIDEN_API_URL = 'widenconfig/basic_credential/widen_permanent_token';
    public const API_CALLED = 'https://trello.thedamconsultants.com/';

    /**
     * Data Helper
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productrepository
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\Bulk $bulk
     * @param \Magento\Catalog\Helper\Image $image
     * @param \Magento\Backend\Helper\Data $data
     */
    public function __construct(
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productrepository,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\Bulk $bulk,
        \Magento\Catalog\Helper\Image $image,
        \Magento\Backend\Helper\Data $data
    ) {
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->productrepository = $productrepository;
        $this->filesystem = $filesystem;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->_curl = $curl;
        $this->_bulk = $bulk;
        $this->file = $file;
        $this->assetRepo = $assetRepo;
        $this->driverFile = $driverFile;
        $this->_registry = $registry;
        $this->_image = $image;
        $this->_data = $data;
        parent::__construct($context);
    }
    /**
     * Get Image Roll
     *
     * @return $this
     */
    public function getBulkImageRoll()
    {
        return $this->_bulk->getMediaAttributes();
    }
    /**
     * Get Backend Name
     *
     * @return $this
     */
    public function getBackendArea()
    {
        return $this->_data->getAreaFrontName();
    }
    /**
     * Get Image Height Widht
     *
     * @return $this
     * @param string $id
     * @param string $attribute
     */
    public function getHeightWidht($id, $attribute)
    {
        return $this->_image->init($id, $attribute);
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
     * Get Product Id
     *
     * @return $this
     * @param string $productId
     */
    public function getProductById($productId)
    {
        return $this->productrepository->getById($productId);
    }
    /**
     * Get Media Url
     *
     * @return $this
     */
    public function getMediaUrl()
    {
        $currentStore =  $this->_storeManager->getStore();
        return $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    /**
     * Get
     *
     * @return $this
     */
    protected function getMediaDirTmpDir()
    {
        $mediaPath = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath('tmp/');
        return $mediaPath;
    }
    /**
     * Get Store Config
     *
     * @return $this
     * @param string $storePath
     * @param string $storeId
     */
    public function getStoreConfig($storePath, $storeId = null)
    {
        return $this->_scopeConfig->getValue($storePath, ScopeInterface::SCOPE_STORE, $storeId);
    }
    /**
     * Get Permanent Token
     *
     * @return $this
     */
    public function getPermanentToken()
    {
        return (string) $this->getStoreConfig(self::PERMANENT_TOKEN);
    }
    /**
     * Get Make Video Image
     *
     * @param string $img
     * @param string $productSku
     * @param string $mediaUrl
     * @return $this
     */
    public function getMakeVideoImage($img, $productSku, $mediaUrl)
    {
        $dir_path = "video_thumbnail";
        $img_dir = BP . '/pub/media/' . $dir_path;
        if (!$this->file->fileExists($img_dir)) {
            $this->file->mkdir($img_dir, 0755, true);
        }
        
        if (!empty($img)) {
            $img_array =  json_decode($img, true);
        
            if (count($img_array) > 0) {
                foreach ($img_array as $key => $item) {
                    if ($item['item_type'] == 'video') {
                        $rand_str=rand();
                        /**
                         * check item url is from local path or not
                         * Need to resize playbutton image -> not proper
                         * Need to find some solution for s3 bucket link because it's not convertable. (Done)
                         */

                        /**check item URL is template or local thumbnail */
                        $template_url = $item["selected_template_url"];
                        
                        if ($template_url != "") {
                            if (strpos($template_url, "Key-Pair-Id") !== false) {
                                $item_url = $template_url;
                                if (!empty($item_url)) {

                                    $fileInfo = $this->file->getPathInfo($item_url);
                                    
                                    $basename = $fileInfo['basename'];
                                    $file_name = explode("?", $basename);
                                    $b_file_name = $file_name[0];
                                    $final_file_name = str_replace("%20", " ", $b_file_name);

                                    $img_url = $img_dir . "/" . $final_file_name;
                                    
                                    if (isset($fileInfo["extension"])) {
                                        $file_extension = $fileInfo["extension"];
                                    } else {
                                        $file_extension = "png";
                                        /* we need to store image in local folder for temp. use */
                                        $final_file_name = $productSku."_".$rand_str.".".$file_extension;
                                        $temp_img_dir_path = $img_dir."/".$final_file_name;

                                        $this->file->write(
                                            $temp_img_dir_path,
                                            $this->driverFile->fileGetContents($item_url)
                                        );
                                        
                                        $item_url = $temp_img_dir_path;
                                        $img_url =  $temp_img_dir_path;
                                    }
                                    /** Below code for check item url size */
                                    $image_properties = getimagesize($item_url);

                                    if (isset($image_properties[0])) {
                                        $image_og_width = (int)$image_properties[0];
                                        $image_og_height = (int)$image_properties[1];
                                        $mine_type = $image_properties["mime"];
                                    } else {
                                        $image_og_width = 900;
                                        $image_og_height = 900;
                                        $mine_type = "image/".$file_extension;
                                    }

                                    /** Below code for check playbutton size */
                                    $play_img_path = BP."/app/code/DamConsultants/Widen/view/adminhtml/web/images/playButton-big.png";

                                    $play_button_url = $this->assetRepo->getUrl("DamConsultants_Widen::images/playButton-big.png");
                                    
                                    if ($file_extension == "png") {
                                        $src = imagecreatefrompng($item_url);
                                    } else {
                                        $src = imagecreatefromjpeg($item_url);
                                    }

                                    $dest = imagecreatefrompng($play_button_url);

                                    imagealphablending($dest, false);
                                    imagesavealpha($dest, false);

                                    imagecopymerge($dest, $src, 0, 0, 0, 0, 600, 338, 60);

                                    /*/header('Content-Type: '.$mine_type);*/

                                    if ($file_extension == "png") {
                                        imagepng($dest, $img_url);
                                    } else {
                                        imagejpeg($dest, $img_url);
                                    }

                                    $final_new_img = $mediaUrl . $dir_path . "/" . $final_file_name;
                                    /* perfect in 195 * 110 */
                                    $res = $this->resize_play_button_image($final_new_img, 160, 90);
                                    imagepng($res, $img_url);
                                    imagedestroy($res);

                                    imagedestroy($dest);
                                    imagedestroy($src);
                              
                                    $img_array[$key]["thum_url"] = $mediaUrl.$dir_path."/".$final_file_name;
                                    $img_array[$key]["selected_template_url"]=$mediaUrl.$dir_path."/".$final_file_name;
                                    
                                }
                            }
                        }
                    }
                }
                return $img_array;
            }
        }
        return $img;
    }
    /**
     * Get Widen Category API
     *
     * @return array $response
     */
    public function getWidencategoryAPI()
    {
        $post_filed_array = [
            "permentant_token" => $this->getPermanentToken()
        ];
        $param_data_json_value = json_encode($post_filed_array);
        $request_url = self::API_CALLED . 'get-widen-categories';
        $jsonData = '{}';
        $this->_curl->setOption(CURLOPT_URL, $request_url);
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $param_data_json_value);
        $this->_curl->addHeader("Content-Type", "application/json");
        $this->_curl->post($request_url, $jsonData);
        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get getCheckboxWiseSearch
     *
     * @return string $response
     * @param string $wcQuery
     * @param string $sortingData
     */
    public function getCheckboxWiseSearch($wcQuery, $sortingData = "")
    {
        $post_filed_array = [
            "permentant_token" => $this->getPermanentToken(),
            "param_data" => [
                "search_query" => $wcQuery,
                "sortingData" => $sortingData
            ]
        ];
        $param_data_json_value = json_encode($post_filed_array);
        $request_url = self::API_CALLED . 'get-category-wise-data';
        $jsonData = '{}';
        $this->_curl->setOption(CURLOPT_URL, $request_url);
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $param_data_json_value);
        $this->_curl->addHeader("Content-Type", "application/json");
        $this->_curl->post($request_url, $jsonData);
        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get getAttributeDefaultData
     *
     * @return string $response
     */
    public function getAttributeDefaultData()
    {
        $post_filed_array = [
            "permentant_token" => $this->getPermanentToken()
        ];
        $param_data_json_value = json_encode($post_filed_array);
        $request_url = self::API_CALLED . 'get-attribute-defaultdata';
        $jsonData = '{}';
        $this->_curl->setOption(CURLOPT_URL, $request_url);
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $param_data_json_value);
        $this->_curl->addHeader("Content-Type", "application/json");
        $this->_curl->post($request_url, $jsonData);
        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * AttributeData
     *
     * @param array $wcQuery
     * @return $this
     */
    public function attributeData($wcQuery)
    {
        $post_filed_array = [
            "permentant_token" => $this->getPermanentToken(),
            "param_data" => [
                "scroll_id" => $wcQuery
            ]
        ];
        $param_data_json_value = json_encode($post_filed_array);
        $request_url = self::API_CALLED . 'get-attribute-data';
        $jsonData = '{}';
        $this->_curl->setOption(CURLOPT_URL, $request_url);
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $param_data_json_value);
        $this->_curl->addHeader("Content-Type", "application/json");
        $this->_curl->post($request_url, $jsonData);
        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get getWidenImageSyncWithProperties
     *
     * @return array $respones_array
     * @param string $sku
     * @param string $properties_details
     */
    public function getWidenImageSyncWithProperties($sku, $properties_details)
    {
        $post_filed_array = [
            "permentant_token" => $this->getPermanentToken(),
            "param_data" => [
                'sku' => $sku,
                'properties_details' => $properties_details
            ]
        ];
        $param_data_json_value = json_encode($post_filed_array);
        $request_url = self::API_CALLED . 'get-sku-wise-sync-data';
        $jsonData = '{}';
        
        $this->_curl->setOption(CURLOPT_URL, $request_url);
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $param_data_json_value);
        $this->_curl->addHeader("Content-Type", "application/json");
        $this->_curl->post($request_url, $jsonData);
        $response = $this->_curl->getBody();
        return $response;
    }

    /**
     * 01-05-2023
     *
     * Get getWidenFilterData
     *
     * @return string $response
     * @param array $param_data_array
     */
    public function getWidenFilterData($param_data_array)
    {

        $post_filed_array = [
            "permentant_token" => $this->getPermanentToken(),
            "param_data" => $param_data_array
        ];

        $param_data_json_value = json_encode($post_filed_array);
        $request_url = self::API_CALLED . 'get-widen-filter-details';
        $jsonData = '{}';
        
        $this->_curl->setOption(CURLOPT_URL, $request_url);
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 0);
        $this->_curl->setOption(CURLOPT_ENCODING, '');
        $this->_curl->setOption(CURLOPT_MAXREDIRS, 10);
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->_curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->_curl->setOption(CURLOPT_POSTFIELDS, $param_data_json_value);
        $this->_curl->addHeader("Content-Type", "application/json");
        $this->_curl->post($request_url, $jsonData);
        $response = $this->_curl->getBody();
        return $response;
    }
    /**
     * Get Make Video Thumb For Sync
     *
     * @param string $img
     * @param string $productSku
     * @param string $mediaUrl
     * @return $this
     */
    public function getMakeVideoasThumbForSync($img, $productSku, $mediaUrl)
    {
        $dir_path = "video_thumbnail";
        $img_dir = BP . '/pub/media/' . $dir_path;
        if (!$this->file->fileExists($img_dir)) {
            $this->file->mkdir($img_dir, 0755, true);
        }
        
        if (!empty($img)) {
            $img_array =  $img;
            
            $template_url = $img_array["template_url"];

            $rand_str=rand();
            /**check item URL is template or local thumbnail */
            
            if ($template_url != "") {
                if (strpos($template_url, "Key-Pair-Id") !== false) {
                    $item_url = $template_url;
                    if (!empty($item_url)) {

                        $fileInfo = $this->file->getPathInfo($item_url);
                        
                        $basename = $fileInfo['basename'];
                        $file_name = explode("?", $basename);
                        $b_file_name = $file_name[0];
                        $final_file_name = str_replace("%20", " ", $b_file_name);

                        $img_url = $img_dir . "/" . $final_file_name;
                        
                        if (isset($fileInfo["extension"])) {
                            $file_extension = $fileInfo["extension"];
                        } else {
                            $file_extension = "png";
                            /* we need to store image in local folder for temp. use */
                            $final_file_name = $productSku."_".$rand_str.".".$file_extension;
                            $temp_img_dir_path = $img_dir."/".$final_file_name;

                            $this->file->write(
                                $temp_img_dir_path,
                                $this->driverFile->fileGetContents($item_url)
                            );
                            
                            $item_url = $temp_img_dir_path;
                            $img_url =  $temp_img_dir_path;
                        }
                        /** Below code for check item url size */
                        $image_properties = getimagesize($item_url);

                        $play_button_url =  $this->assetRepo->getUrl("DamConsultants_Widen::images/playButton-full-big.png");
                        
                        if ($file_extension == "png") {
                            $src = imagecreatefrompng($item_url);
                        } else {
                            $src = imagecreatefromjpeg($item_url);
                        }

                        $dest = imagecreatefrompng($play_button_url);

                        imagealphablending($dest, false);
                        imagesavealpha($dest, false);

                        imagecopymerge($dest, $src, 0, 0, 0, 0, 600, 338, 60);

                        if ($file_extension == "png") {
                            imagepng($dest, $img_url);
                        } else {
                            imagejpeg($dest, $img_url);
                        }

                        $final_new_img = $mediaUrl . $dir_path . "/" . $final_file_name;
                        /* perfect in 195 * 110 */
                        $res = $this->resize_play_button_image($final_new_img, 160, 90);
                        imagepng($res, $img_url);
                        imagedestroy($res);
                        
                        imagedestroy($dest);
                        imagedestroy($src);
                    
                        $img_array["template_url"] = $mediaUrl.$dir_path."/".$final_file_name;
                    }
                }
            }
        }
        return $img_array;
    }

    /**
     * 01-05-2023
     *
     * Resize static button size
     *
     * @param string $file_name
     * @param string $width
     * @param string $height
     * @param bool $crop
     * @return $this
     */
    public function resize_play_button_image($file_name, $width, $height, $crop = false)
    {
        list($wid, $ht) = getimagesize($file_name);
        $r = $wid / $ht;
        if ($crop) {
            if ($wid > $ht) {
                $wid = ceil($wid-($width*abs($r-$width/$height)));
            } else {
                $ht = ceil($ht-($ht*abs($r-$width/$height)));
            }
            $new_width = $width;
            $new_height = $height;
        } else {
            /*if ($width/$height > $r) {
                $new_width = $height*$r;
                $new_height = $height;
            } else {
                $new_height = $width/$r;
                $new_width = $width;
            }*/
            $new_width = $width;
            $new_height = $height;
        }
        $source = imagecreatefrompng($file_name);
        $dst = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($dst, $source, 0, 0, 0, 0, $new_width, $new_height, $wid, $ht);
        return $dst;
    }
}
