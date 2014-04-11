<?php
class Devils_Colors_Block_Adminhtml_Color_Import extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'devils_colors';
        $this->_controller = 'adminhtml_color';

        $this->_removeButton('save');
        $this->_addButton('import', array('label' => Mage::helper('devils_colors')->__('Run Import'), 'onclick' => '$(\'importForm\').submit();', 'class' => 'save'));
    }

    public function getHeaderText()
    {
        return Mage::helper('devils_colors')->__('New Import');
    }
}