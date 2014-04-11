<?php
class Devils_Colors_Block_Adminhtml_Color_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('devils_colors_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('devils_colors')->__('Manage Color Swatch'));
	}
	
	protected function _beforeToHtml()
	{
		$this->addTab('form_section', array(
			'label'     => Mage::helper('devils_colors')->__('Details'),
			'title'     => Mage::helper('devils_colors')->__('Details'),
			'content'   => $this->getLayout()->createBlock('devils_colors/adminhtml_color_edit_tab_form')->toHtml())
		);
		
		return parent::_beforeToHtml();
	}
}