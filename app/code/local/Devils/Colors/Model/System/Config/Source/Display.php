<?php
class Devils_Colors_Model_System_Config_Source_Display
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('devils_colors')->__('Resize')),
            array('value' => 1, 'label' => Mage::helper('devils_colors')->__('Clip'))
        );
    }
}