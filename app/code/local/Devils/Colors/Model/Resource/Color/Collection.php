<?php
class Devils_Colors_Model_Resource_Color_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    public function _construct()
    {
        $this->_init('devils_colors/color');
    }
}