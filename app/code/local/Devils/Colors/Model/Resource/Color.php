<?php
class Devils_Colors_Model_Resource_Color extends Mage_Core_Model_Resource_Db_Abstract {
    public function _construct()
    {
        $this->_init('devils_colors/color', 'entity_id');
    }
}