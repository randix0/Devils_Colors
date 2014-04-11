<?php

class Devils_Colors_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Options extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('devils/colors/catalog/product/attribute/options.phtml');
    }

    public function isColor()
    {
        return (bool)stristr($this->getAttributeObject()->getAttributeCode(), 'color');
    }

    public function getAllColors()
    {
        return Mage::getModel('devils_colors/color')->getCollection()
            ->setOrder('name', 'asc');
    }

    public function getSelectedColor($value, $attr)
    {
        $collection = Mage::getModel('devils_colors/attribute')->getCollection()
            ->addFieldToFilter('attribute_id', array('eq' => $attr))
            ->addFieldToFilter('option_id', array('eq' => $value->getId()))
            ->setPageSize(1);

        if($collection->getSize() > 0){
            return $collection->getFirstItem()->getColorId();
        }
        return false;
    }
}
