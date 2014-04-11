<?php
class Devils_Colors_Block_Adminhtml_Color_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('colorGrid');
		$this->setDefaultSort('entity_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('devils_colors/color')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('entity_id', array(
			'header'    => Mage::helper('devils_colors')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'entity_id')
		);
	
		$this->addColumn('name', array(
			'header'    => Mage::helper('devils_colors')->__('Name'),
			'align'     =>'left',
			'index'     => 'name')
		);
		
		$this->addColumn('action',
			array(
				'header'    =>  Mage::helper('devils_colors')->__('Action'),
				'width'     => '100',
				'type'      => 'action',
				'getter'    => 'getId',
				'actions'   => array(
					array(
					'caption'   => Mage::helper('devils_colors')->__('Edit'),
					'url'       => array('base' => '*/*/edit'),
					'field'     => 'id')
				),
				'filter'    => false,
				'sortable'  => false,
				'index'     => 'stores',
				'is_system' => true,
		));
	
		return parent::_prepareColumns();
	}
	
	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('devils_colors');
		
		$this->getMassactionBlock()->addItem('delete', array(
			'label'    => Mage::helper('devils_colors')->__('Delete'),
			'url'      => $this->getUrl('*/*/massDelete'),
			'confirm'  => Mage::helper('devils_colors')->__('Are you sure you want to delete the selected colors?'))
		);

		return $this;
	}
	
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}