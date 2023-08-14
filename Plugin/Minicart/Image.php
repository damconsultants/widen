<?php

namespace DamConsultants\Widen\Plugin\Minicart;

class Image
{

    /**
     * Image
     * @param \Magento\Framework\Registry $Registry
     * @param \Magento\Catalog\Model\Product $product
     */
    public function __construct(
        \Magento\Framework\Registry $Registry,
        \Magento\Catalog\Model\Product $product
    ) {

        $this->_registry = $Registry;
        $this->product = $product;
    }

    /**
     * Around Get Item Data
     *
     * @param \Magento\Checkout\CustomerData\AbstractItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    public function aroundGetItemData(
        \Magento\Checkout\CustomerData\AbstractItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item $item
    ) {

        $data = $proceed($item);
        $productId = $item->getProduct()->getId();
        $product = $this->product->load($productId);
        $widenImage = $product->getData('widen_multi_img');
        if ($widenImage != "") {
            $json_value = json_decode($widenImage, true);
            $thumbnail = 'thumbnail';
            if (!empty($json_value)) {
                foreach ($json_value as $values) {
                    if (isset($values['image_role'])) {
                        foreach ($values['image_role'] as $image_role) {
                            if ($image_role == $thumbnail) {
                                $image_values = trim($values['thum_url']);
                                if (($values['height'] != "") && ($values['width'] != "")) {
                                    $image_values = $values['selected_template_url'].'&h='.$values['height'].'&w='.$values['width'];
                                }
                                $data['product_image']['src'] = $image_values;
                                $altText = trim($values['altText']);
                                $data['product_image']['alt'] = $altText;
                            }
                        }
                    }

                }
            } else {
                $data['product_image']['src'];
            }

        } else {

            $data['product_image']['src'];
        }
        return $data;
    }
}
