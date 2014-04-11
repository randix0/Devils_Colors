<?php
class Devils_Colors_Block_Adminhtml_Color_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
	
		$this->_objectId = 'id';
		$this->_blockGroup = 'devils_colors';
		$this->_controller = 'adminhtml_color';
		
		$this->_updateButton('save', 'label', Mage::helper('devils_colors')->__('Save Color'));
		$this->_updateButton('delete', 'label', Mage::helper('devils_colors')->__('Delete Color'));
		
		$this->_addButton('saveandcontinue', array(
			'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
			'onclick'   => 'saveAndContinueEdit()',
			'class'     => 'save',
		), -100);
	
		$this->_formScripts[] = "
		function toggleEditor() {
			if (tinyMCE.getInstanceById('devils_colors_content') == null)
			{
				tinyMCE.execCommand('mceAddControl', false, 'devils_colors_content');
			}else{
				tinyMCE.execCommand('mceRemoveControl', false, 'devils_colors_content');
			}
		}
		
		function saveAndContinueEdit(){
			editForm.submit($('edit_form').action+'back/edit/');
		}";
	}
	
	public function getHeaderText()
	{
		if( Mage::registry('devils_colors_data') && Mage::registry('devils_colors_data')->getId())
		{
			return Mage::helper('devils_colors')->__("Edit Swatch '%s'", $this->htmlEscape(Mage::registry('devils_colors_data')->getName()));
		}else{
			return Mage::helper('devils_colors')->__('Create Swatch');
		}
	}
}