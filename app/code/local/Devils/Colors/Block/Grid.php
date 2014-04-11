<?php

class Devils_Colors_Block_Grid extends Mage_Catalog_Block_Product_View_Type_Configurable
{

    public function getOptionsHtml($attribute, $product)
    {
        $collection = Mage::getModel('devils_colors/options')->getAllOptions($attribute, $product);
        $grid = $this->getLayout()->createBlock('devils_colors/options')
            ->setBlockId('devils_colors.options.config' . $this->_getAttributeId($attribute))
            ->setOptions($collection)
            ->setSuperAttribute($attribute)
            ->toHtml();

        return $grid;
    }

    public function getOptionJsonConfig($product, $attributes)
    {
        return Mage::getModel('devils_colors/options')->getOptionJsonConfig($product, $attributes);
    }

    public function getHaloColor()
    {
        return Mage::getStoreConfig('devils_colors/general/halo');
    }

    protected function _getAttributeId($attr)
    {
        if(is_numeric($attr)){
            return $attr;
        }

        return $attr->getAttributeId();
    }

    protected function _getHelper()
    {
        return Mage::helper('devils_colors');
    }
}