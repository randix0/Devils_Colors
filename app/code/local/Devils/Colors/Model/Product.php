<?php
class Devils_Colors_Model_Product extends Mage_Catalog_Model_Product
{
	/**
	 * Attribute code
	 *
	 * @var string
	 */
	private $_attributeCode;

	/**
	 * Retrieve a collection of associated simple products
	 * sorted by the size attribute in ascending order
	 *
	 * @return Varien_Data_Collection
	 */
	public function getConfigOptions()
	{
		$collection = Mage::getModel('catalog/product_type_configurable')
			->getUsedProductCollection($this)
			->addAttributeToSelect($this->getAttributeCode());

		$options = new Varien_Data_Collection();
		foreach($this->getAllOptions() as $option){
			foreach($collection as $item){
				if($item->getData($this->getAttributeCode()) == $option['value']){
					$options->addItem($item);
					$added[] = $option['value'];
				}
			}
		}
		
		return $options;
	}

	/**
	 * Returns all option values/labels for the current size attribute
	 *
	 * @return array
	 */
	public function getAllOptions()
	{
		return $this->_getHelper()->cleanArray(
			$this->_getAttribute()->getSource()->getAllOptions(true, true)
		);
	}

	/**
	 * Return the attribute code that most closely matches
	 * a size attribute
	 *
	 * @return string
	 */
	public function getAttributeCode()
	{
		if(!$this->_attributeCode){
			$configAttribute = $this->getTypeInstance(true)->getConfigurableAttributesAsArray($this);
			foreach($configAttribute as $attr){
				if(stripos($attr['attribute_code'], 'size') !== false){
					$this->_attributeCode = $attr['attribute_code'];
					break;
				}
			}
		}
		return $this->_attributeCode;
	}

	/**
	 * Get super attribute id of the attribute
	 *
	 * @return int
	 */
	public function getSuperAttributeId()
	{
		return (int)$this->_getAttribute()->getAttributeId();
	}

	/**
	 * Returns option label based on given attribute option id
	 *
	 * @param mixed
	 * @return string
	 */
	public function getAttributeLabel($id)
	{
		foreach($this->getAllOptions() as $attr){
			if($id == $attr['value']){
				return $attr['label'];
			}
		}
		return '';
	}

	/**
	 * Get attribute instance
	 *
	 * @return Mage_Eav_Model_Entity_Attribute_Abstract
	 */
	protected function _getAttribute()
	{
		return Mage::getModel('eav/config')->getAttribute('catalog_product', $this->getAttributeCode());
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