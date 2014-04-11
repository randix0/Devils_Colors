<?php

class Devils_Colors_Block_Adminhtml_Color extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_color';
        $this->_blockGroup = 'devils_colors';
        $this->_headerText = Mage::helper('devils_colors')->__('Color Swatch Manager');
        $this->_addButtonLabel = Mage::helper('devils_colors')->__('Add Swatch');
        $this->_addButton('import', array('label' => 'Import', 'onclick' => 'setLocation(\'' . $this->getUrl('*/*/import') . '\');'));
        parent::__construct();
    }
}