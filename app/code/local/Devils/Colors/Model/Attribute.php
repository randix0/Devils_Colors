<?php
class Devils_Colors_Model_Attribute extends Mage_Core_Model_Abstract {
    public function _construct()
    {
        parent::_construct();
        $this->_init('devils_colors/attribute');
    }

    public function loadByOptionId($id)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('option_id', array('eq' => $id))
            ->setPageSize(1);

        if($collection->getSize() > 0){
            return $this->load($collection->getFirstItem()->getId());
        }

        return $this;
    }
}