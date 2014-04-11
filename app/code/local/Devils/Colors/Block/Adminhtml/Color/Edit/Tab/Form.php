<?php
class Devils_Colors_Block_Adminhtml_Color_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('devils_colors_form', array('legend' => Mage::helper('devils_colors')->__('Details')));
		
		$fieldset->addField('name', 'text', array(
			'label'     => Mage::helper('devils_colors')->__('Name'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'name')
		);

		$fieldset->addField('value', 'text', array(
			'label'     => Mage::helper('devils_colors')->__('Color'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'value')
		);

		$fieldset->addField('file', 'image', array(
			'label'     => Mage::helper('devils_colors')->__('File'),
			'required'  => false,
			'name'      => 'file')
		);
		
		if(Mage::getSingleton('adminhtml/session')->getDevilsColorsData())
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getDevilsColorsData());
			Mage::getSingleton('adminhtml/session')->setDevilsColorsData(null);
		}else if(Mage::registry('devils_colors_data')){
			$form->setValues(Mage::registry('devils_colors_data')->getData());
		}

		if(($id = Mage::app()->getRequest()->getParam('id'))){
			$image = $form->getElement('file')->getValue();
			$form->getElement('file')->setValue(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'devils' . DS . 'devils_colors' . DS . 'colors' . DS . $id . DS . $image);
		}
		return parent::_prepareForm();
	}
}