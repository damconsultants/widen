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

namespace DamConsultants\Widen\Block\Product\View;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\ImagesConfigFactoryInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\ArrayUtils;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    /**
     * @var \Magento\Framework\Config\View
     */
    protected $configView;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var array
     */
    private $galleryImagesConfig;

    /**
     * @var ImagesConfigFactoryInterface
     */
    private $galleryImagesConfigFactory;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    public $request;

    /**
     * Gallery
     * @param \Magento\Framework\App\Request\Http $request
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $Registry
     * @param ImagesConfigFactoryInterface $imagesConfigFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $galleryImagesConfig
     * @param UrlBuilder $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        Context $context,
        ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Registry $Registry,
        ImagesConfigFactoryInterface $imagesConfigFactory = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $galleryImagesConfig = [],
        UrlBuilder $urlBuilder = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $data,
            $imagesConfigFactory,
            $galleryImagesConfig,
            $urlBuilder
        );
        $this->jsonEncoder = $jsonEncoder;
        $this->galleryImagesConfigFactory = $imagesConfigFactory ?: ObjectManager::getInstance()
            ->get(ImagesConfigFactoryInterface::class);
        $this->galleryImagesConfig = $galleryImagesConfig;
        $this->imageUrlBuilder = $urlBuilder ?? ObjectManager::getInstance()->get(UrlBuilder::class);
        $this->_registry = $Registry;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
    }

    /**
     * Retrieve collection of gallery images
     *
     * @return Collection
     */
    public function getGalleryImages()
    {
        $product = $this->getProduct();
        $images = $product->getMediaGalleryImages();
        if (!$images instanceof \Magento\Framework\Data\Collection) {
            return $images;
        }

        foreach ($images as $image) {
            $galleryImagesConfig = $this->getGalleryImagesConfig()->getItems();
            foreach ($galleryImagesConfig as $imageConfig) {
                $image->setData(
                    $imageConfig->getData('data_object_key'),
                    $this->imageUrlBuilder->getUrl($image->getFile(), $imageConfig['image_id'])
                );
            }
        }

        return $images;
    }

    /**
     * Return magnifier options
     *
     * @return string
     */
    public function getMagnifier()
    {
        return $this->jsonEncoder->encode($this->getVar('magnifier'));
    }

    /**
     * Return breakpoints options
     *
     * @return string
     */
    public function getBreakpoints()
    {
        return $this->jsonEncoder->encode($this->getVar('breakpoints'));
    }

    /**
     * Retrieve product images in JSON format
     *
     * @return string
     */
    public function getGalleryImagesJson()
    {
        $product = $this->_registry->registry('product');
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $use_widen_cdn = $product->getUseWidenCdn();
        $use_widen_both_image = $product->getUseWidenBothImage();
        $widen_image = $product->getWidenMultiImg();
        $imagesItems = [];
        $role_image = 0;
        $magento_img = $this->getGalleryImages();
        $magento_config = $this->getGalleryImagesConfig()->getItems();
        if ($use_widen_both_image == 1) { /*Both Image*/
            foreach ($magento_img as $image) {
                $imageItem = new DataObject([
                    'thumb' => $image->getData('small_image_url'),
                    'img' => $image->getData('medium_image_url'),
                    'full' => $image->getData('large_image_url'),
                    'caption' => ($role_image == 1) ? 0 :($image->getLabel() ?: $this->getProduct()->getName()),
                    'position' => $image->getData('position'),
                    'isMain' => $this->isMainImage($image),
                    'type' => str_replace('external-', '', $image->getMediaType()),
                    'videoUrl' => $image->getVideoUrl(),
                ]);
                foreach ($magento_config as $imageConfig) {
                    $imageItem->setData(
                        $imageConfig->getData('json_object_key'),
                        $image->getData($imageConfig->getData('data_object_key'))
                    );
                }
                $imagesItems[] = $imageItem->toArray();
            }
            if (!empty($widen_image)) {
                $json_value = json_decode($widen_image, true);
                foreach ($json_value as $key => $values) {
                    $thum_image_values = trim($values['thum_url']);

                    if ($values["item_type"] == "video") {
                        $image_values = trim($values['item_url']);
                    } else {
                        $image_values = trim($values['thum_url']);
                    }

                    if (($values['height'] > 0) && ($values['width'] > 0)) {
                        $image_values = $values['selected_template_url'].'&h='.$values['height'].'&w='.$values['width'];
                    }

                    if ($values['item_type'] == 'image' && isset($values['image_role'])) {
                        foreach ($values['image_role'] as $image_role) {
                            if ($image_role ==  'image') {
                                $role_image = 1;
                            }
                        }
                    }
                    $imageItem = new DataObject([
                        'thumb' => $thum_image_values,
                        'img' => ($values['item_type'] == 'image') ? $image_values : $thum_image_values,
                        'full' => ($values['item_type'] == 'image') ? $image_values : $thum_image_values,
                        'caption' => $this->getProduct()->getName(),
                        'position' => $key + 1,
                        'isMain' =>$role_image,
                        'type' => ($values['item_type'] == 'image') ? 'image' : 'video',
                        'videoUrl' => ($values['item_type'] == 'video') ? $image_values : null,
                        "src" => ($values['item_type'] == 'video') ? $image_values : null,
                        "type" => ($values['item_type'] == 'video') ? 'iframe' : null
                    ]);
                    $imagesItems[] = $imageItem->toArray();
                }
            }
            
        } elseif ($use_widen_cdn == 1) { /*CDN Image*/
            if (!empty($widen_image)) {
                $json_value = json_decode($widen_image, true);
                $role_image = 0;
                foreach ($json_value as $key => $values) {
                    $thum_image_values = trim($values['thum_url']);

                    if ($values["item_type"] == "video") {
                        $image_values = trim($values['item_url']);
                    } else {
                        $image_values = trim($values['thum_url']);
                    }

                    if (($values['height'] > 0) && ($values['width'] > 0)) {
                        $image_values = $values['selected_template_url'].'&h='.$values['height'].'&w='.$values['width'];
                    }

                    if ($values['item_type'] == 'image' && isset($values['image_role'])) {
                        foreach ($values['image_role'] as $image_role) {
                            if ($image_role ==  'image') {
                                $role_image = 1;
                            }
                        }
                    }
                    $imageItem = new DataObject([
                        'thumb' => $thum_image_values,
                        'img' => ($values['item_type'] == 'image') ? $image_values : $thum_image_values,
                        'full' => ($values['item_type'] == 'image') ? $image_values : $thum_image_values,
                        'caption' => $this->getProduct()->getName(),
                        'position' => $key + 1,
                        'isMain' =>$role_image,
                        'type' => ($values['item_type'] == 'image') ? 'image' : 'video',
                        'videoUrl' => ($values['item_type'] == 'video') ? $image_values : null,
                        "src" => ($values['item_type'] == 'video') ? $image_values : null,
                        "type" => ($values['item_type'] == 'video') ? 'iframe' : null
                    ]);
                    $imagesItems[] = $imageItem->toArray();
                }
            } else {
                /* CDN link empty */
                foreach ($magento_img as $image) {
                    $imageItem = new DataObject([
                        'thumb' => $image->getData('small_image_url'),
                        'img' => $image->getData('medium_image_url'),
                        'full' => $image->getData('large_image_url'),
                        'caption' => ($image->getLabel() ?: $this->getProduct()->getName()),
                        'position' => $image->getData('position'),
                        'isMain' => $this->isMainImage($image),
                        'type' => str_replace('external-', '', $image->getMediaType()),
                        'videoUrl' => $image->getVideoUrl(),
                    ]);
                    foreach ($this->getGalleryImagesConfig()->getItems() as $imageConfig) {
                        $imageItem->setData(
                            $imageConfig->getData('json_object_key'),
                            $image->getData($imageConfig->getData('data_object_key'))
                        );
                    }
                    $imagesItems[] = $imageItem->toArray();
                }

            }
        } else {
            foreach ($magento_img as $image) {
                $imageItem = new DataObject([
                    'thumb' => $image->getData('small_image_url'),
                    'img' => $image->getData('medium_image_url'),
                    'full' => $image->getData('large_image_url'),
                    'caption' => ($image->getLabel() ?: $this->getProduct()->getName()),
                    'position' => $image->getData('position'),
                    'isMain' => $this->isMainImage($image),
                    'type' => str_replace('external-', '', $image->getMediaType()),
                    'videoUrl' => $image->getVideoUrl(),
                ]);
                foreach ($magento_config as $imageConfig) {
                    $imageItem->setData(
                        $imageConfig->getData('json_object_key'),
                        $image->getData($imageConfig->getData('data_object_key'))
                    );
                }
                $imagesItems[] = $imageItem->toArray();
            }
        }
        return json_encode($imagesItems);
    }

    /**
     * Retrieve gallery url
     *
     * @param null|\Magento\Framework\DataObject $image
     * @return string
     */
    public function getGalleryUrl($image = null)
    {
        $params = ['id' => $this->getProduct()->getId()];
        if ($image) {
            $params['image'] = $image->getValueId();
        }
        return $this->getUrl('catalog/product/gallery', $params);
    }

    /**
     * Is product main image
     *
     * @param \Magento\Framework\DataObject $image
     * @return bool
     */
    public function isMainImage($image)
    {
        $product = $this->getProduct();
        return $product->getImage() == $image->getFile();
    }

    /**
     * Returns image attribute
     *
     * @param string $imageId
     * @param string $attributeName
     * @param string $default
     * @return string
     */
    public function getImageAttribute($imageId, $attributeName, $default = null)
    {
        $attributes = $this->getConfigView()
            ->getMediaAttributes('Magento_Catalog', Image::MEDIA_TYPE_CONFIG_NODE, $imageId);
        return $attributes[$attributeName] ?? $default;
    }

    /**
     * Retrieve config view
     *
     * @return \Magento\Framework\Config\View
     */
    private function getConfigView()
    {
        if (!$this->configView) {
            $this->configView = $this->_viewConfig->getViewConfig();
        }
        return $this->configView;
    }

    /**
     * Returns image gallery config object
     *
     * @return Collection
     */
    private function getGalleryImagesConfig()
    {
        if (false === $this->hasData('gallery_images_config')) {
            $galleryImageConfig = $this->galleryImagesConfigFactory->create($this->galleryImagesConfig);
            $this->setData('gallery_images_config', $galleryImageConfig);
        }

        return $this->getData('gallery_images_config');
    }
}
