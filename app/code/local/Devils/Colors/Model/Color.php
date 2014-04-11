<?php
class Devils_Colors_Model_Color extends Mage_Core_Model_Abstract {
    public function _construct()
    {
        parent::_construct();
        $this->_init('devils_colors/color');
    }

    public function loadByName($name)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('name', array('eq' => $name))
            ->setPageSize(1);

        if($collection->getSize() > 0){
            return $this->load($collection->getFirstItem()->getId());
        }

        return $this;
    }
}