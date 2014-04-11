<?php
class Devils_Colors_Block_Adminhtml_Color_Import_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
			'id' => 'importForm',
			'action' => $this->getUrl('*/*/run'),
			'method' => 'post',
			'enctype' => 'multipart/form-data')
		);
		
		$fieldset = $form->addFieldset('devils_colors_form', array('legend' => Mage::helper('devils_colors')->__('Import')));
		
		$fieldset->addField('import', 'file', array(
			'label'     => Mage::helper('devils_colors')->__('Import File'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'import')
		);

		$fieldset->addField('first_row', 'checkbox', array(
			'label'     => Mage::helper('devils_colors')->__('Column names in first row'),
			'required'  => false,
			'value'		=> 1,
			'name'      => 'first_row')
		);
		
		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
	}
}