<?php
class Devils_Colors_Model_Options extends Mage_Core_Model_Abstract
{
    protected $_options;
    protected $_attribute;

    public function getAllOptions($attributeId, $product)
    {
        $optionCollection = new Varien_Data_Collection();
        $superAttrId = $attributeId;
        $options = array();

        if($attributeId instanceof Mage_Catalog_Model_Product_Type_Configurable_Attribute){
            $superAttrId = $attributeId->getAttributeId();
        }

        if(is_numeric($product)){
            $product = Mage::getModel('catalog/product')->load($product);
        }

        $attributeCode = $this->_getAttributeCode($superAttrId);

        $this->_attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
        $allOptions = $this->_attribute->getSource()->getAllOptions(true, false);

        $collection = Mage::getModel('catalog/product_type_configurable')
            ->getUsedProductCollection($product)
            ->addAttributeToSelect($this->_attribute->getAttributeCode())
            ->joinField(
                'qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );

        $optionIds = array();
        $optionLabels = array();
        $products = array();

        foreach($collection as $p){
            $optionId = $p->getData($this->_attribute->getAttributeCode());
            $optionIds[] = $optionId;
            $products[$optionId] = $p;
        }

        $options = Mage::getModel('eav/entity_attribute_option')->getCollection()
            ->setIdFilter($optionIds)
            ->setPositionOrder();

        foreach($allOptions as $data){
            $optionLabels[$data['value']] = $data['label'];
        }

        foreach($options as $option){
            $product = $products[$option->getOptionId()];
            $item = new Varien_Object();
            $item->setOptionId($option->getOptionId());
            $item->setLabel(($this->_getHelper()->canTranslate($this->_attribute->getAttributeCode()) ? $this->_getHelper()->translateLabel($optionLabels[$option->getOptionId()]) : $optionLabels[$option->getOptionId()]));
            $item->setQty((int)$product->getQty());
            $item->setIsLow($product->getQty() < $this->_getHelper()->getStockNoticeLevel());
            $item->setIsDisabled(!$product->isSalable());
            $item->setIsColor((stristr($this->_attribute->getAttributeCode(), 'color') !== false ? true : false));
            $item->setBackground($this->_getBackground($option->getOptionId()));
            $item->setProductIds($this->_getAvailableProductIds($collection, $option->getOptionId()));
            $optionCollection->addItem($item);
        }

        return $optionCollection;
    }

    /**
     * Retrieve a list of all product ids that are available for purchase
     * when the given option is selected
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Type_Configurable_Product_Collection $collection
     * @param int $option
     * @return array
     */
    protected function _getAvailableProductIds($collection, $option)
    {
        $ids = array();
        foreach($collection as $product){
            if($product->getData($this->_attribute->getAttributeCode()) == $option){
                $ids[] = $product->getId();
            }
        }
        return $ids;
    }

    /**
     * Retrieve attribute code provided a super attribute id
     *
     * @param int $attributeId
     * @return string
     */
    protected function _getAttributeCode($attributeId)
    {
        return Mage::getModel('eav/entity_attribute')
            ->load($attributeId)
            ->getAttributeCode();
    }

    /**
     * Get the options background css provided the option id
     *
     * @param int $optionId
     * @return string
     */
    protected function _getBackground($optionId)
    {
        $attr = Mage::getModel('devils_colors/attribute')->loadByOptionId($optionId);
        if($attr->getColorId()){
            $background = '';
            $color = Mage::getModel('devils_colors/color')->load($attr->getColorId());
            $path = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'devils' . DS . 'devils_colors' . DS . 'colors' . DS . $color->getId() . DS;
            if(trim($color->getFile()) != '' && file_exists($path . $color->getFile())){
                $background .= sprintf('background-image: url(\'%s\');', $this->_getHelper()->getSwatchImage($color->getId(), $color->getFile()));
            }
            $background .= sprintf('background-color: %s;', $color->getValue());
            return $background;
        }
        return '';
    }

    /**
     * Get the options background css provided the option id
     *
     * @param int $optionId
     * @return string
     * This is for the color box on product listing page
     */
    public function getBackgroundlist($optionId)
    {
        $attr = Mage::getModel('devils_colors/attribute')->loadByOptionId($optionId);
        if($attr->getColorId()){
            $background = '';
            $color = Mage::getModel('devils_colors/color')->load($attr->getColorId());
            $path = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'devils' . DS . 'devils_colors' . DS . 'colors' . DS . $color->getId() . DS;
            if(trim($color->getFile()) != '' && file_exists($path . $color->getFile())){
                $background .= sprintf('background-image: url(\'%s\');', $this->_getHelper()->getSwatchImage($color->getId(), $color->getFile()));
            }
            $background .= sprintf('background-color: %s;', $color->getValue());

            return $background;
        }
        return '';
    }

    /**
     * Retrieve all possible options for all attributes,
     * this includes data such as quantity, image, and label
     *
     * @param Devils_Colors_Model_Product $product
     * @param Devils_Colors_Model_Resource_Product_Type_Configurable_Attribute_Collection $attributes
     * @return string
     */
    public function getOptionJsonConfig($product, $attributes = array())
    {
        $config = array();
        $collection = Mage::getModel('catalog/product_type_configurable')
            ->getUsedProductCollection($product)
            ->addAttributeToSelect(array('name', 'image'));

        foreach($attributes as $attr){
            $code = $this->_getAttributeCode($attr->getAttributeId());
            $collection->addAttributeToSelect($code);
        }

        $collection->joinField(
            'qty',
            'cataloginventory/stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );

        $config['show_notice'] = $this->_getHelper()->getShowNotice();
        $config['notice_msg'] = $this->_getHelper()->getNoticeMessage();
        $config['product_id'] = $product->getId();
        $config['logged_in'] = Mage::getSingleton('customer/session')->isLoggedIn();
        $config['symbol'] = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();

        foreach($collection as $item){
            $_product = Mage::getModel('catalog/product')->load($item->getId());
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);

            $qty = ($stockItem->getBackorders() == 0) ? $item->getQty() : 1000;

            $config[$item->getId()] = array(
                'isLow' => $qty < $this->_getHelper()->getStockNoticeLevel(),
                'qty' => (int)$qty,
                //'image' => (string)Mage::helper('catalog/image')->init($_product, 'image')->keepFrame(false)->constrainOnly(true)->resize(420),
                'image' => (string)Mage::helper('catalog/image')->init($_product, 'image')->keepFrame(true)->constrainOnly(true)->resize(410,520),
                'large_image' => (string)Mage::helper('catalog/image')->init($_product, 'image')->keepFrame(false)->constrainOnly(true)
            );
        }

        return Mage::helper('core')->jsonEncode($config);
    }

    /**
     * Retrieve helper instance
     *
     * @return Devils_Colors_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('devils_colors');
    }
}