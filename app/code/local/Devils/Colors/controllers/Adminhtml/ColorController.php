<?php
class Devils_Colors_Adminhtml_ColorController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('devils_colors/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Configurable Grid'), Mage::helper('adminhtml')->__('Color Manager'));
	
		return $this;
	}
	
	public function indexAction()
	{
		$this->_initAction();
		$this->_addContent($this->getLayout()->createBlock('devils_colors/adminhtml_color'));
		$this->renderLayout();
	}
	
	public function editAction()
	{
		$id = $this->getRequest()->getParam('id');
		$model = Mage::getModel('devils_colors/color')->load($id);
		
		if($model->getId() || $id == 0)
		{
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if(!empty($data))
			{
				$model->setData($data);
			}
	
			Mage::register('devils_colors_data', $model);
	
			$this->loadLayout();
			$this->_setActiveMenu('devils_colors/items');
			
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Configurable Grid'), Mage::helper('adminhtml')->__('Color Manager'));
			
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			
			$this->_addContent($this->getLayout()->createBlock('devils_colors/adminhtml_color_edit'))
				 ->_addLeft($this->getLayout()->createBlock('devils_colors/adminhtml_color_edit_tabs'));
			
			$this->renderLayout();
		}else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('devils_colors')->__('Color does not exist'));
			$this->_redirect('*/*/');
		}
	}
	
	public function newAction()
	{
		$this->loadLayout();
		$this->_setActiveMenu('devils_colors/items');
		
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Configurable Grid'), Mage::helper('adminhtml')->__('Color Manager'));
		
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		
		$this->_addContent($this->getLayout()->createBlock('devils_colors/adminhtml_color_edit'))
			 ->_addLeft($this->getLayout()->createBlock('devils_colors/adminhtml_color_edit_tabs'));
		
		$this->renderLayout();
	}
	
	public function saveAction()
	{
		if($data = $this->getRequest()->getPost()){
			unset($data['file']);
			$color = Mage::getModel('devils_colors/color');
			$color->setData($data)->setId($this->getRequest()->getParam('id'));
			
			try
			{
				$color->save();

				if(isset($_FILES['file']) && $_FILES['file']['name'] != ''){
					$uploader = new Varien_File_Uploader($_FILES['file']);
					$uploader->setAllowedExtensions(array('png', 'jpg', 'gif'));
					$uploader->setAllowRenameFiles(false);
					$uploader->setFilesDispersion(false);
					$path = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'devils' . DS . 'devils_colors' . DS . 'colors' . DS . $color->getId() . DS;
					$saved = $uploader->save($path, $_FILES['file']['name']);

					if($saved){
						$color->setFile($_FILES['file']['name'])->save();
					}else{
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('devils_colors')->__('Unfortunately, the image could not be uploaded.'));
					}
				}
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('devils_colors')->__('%s was successfully saved', $color->getName()));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
	
				if ($this->getRequest()->getParam('back'))
				{
					$this->_redirect('*/*/edit', array('id' => $color->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			}catch(Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setConfiggridData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('devils_colors')->__('Unable to find item to save'));
		$this->_redirect('*/*/');
	}
	
	public function deleteAction()
	{
		if($this->getRequest()->getParam('id') > 0)
		{
			try
			{
				$id = $this->getRequest()->getParam('id');
				$color = Mage::getModel('devils_colors/color');
				$color->setId($id)->delete();

				$colorPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'devils' . DS . 'devils_colors' . DS . 'colors' . DS . $id;
				$cachePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'devils' . DS . 'devils_colors' . DS . 'cache' . DS . $id;
				$this->_clearDir($colorPath);
				$this->_clearDir($cachePath);
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Swatch was successfully deleted'));
				$this->_redirect('*/*/');
			}catch(Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
	
	public function gridAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('devils_colors/adminhtml_color_grid')->toHtml()
		);
	}
	
	public function importAction()
	{
		Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('devils_colors')->__('If importing swatch images with colors, ensure your swatch images are located in the /media/import directory first.'));
		$this->loadLayout();
		$this->_setActiveMenu('devils_colors/items');
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Color Swatch Manager'), Mage::helper('adminhtml')->__('Import'));
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->_addContent($this->getLayout()->createBlock('devils_colors/adminhtml_color_import'))
            ->_addContent($this->getLayout()->createBlock('devils_colors/adminhtml_color_import_form'));
		$this->renderLayout();
	}
	
	public function runAction()
	{
		if(!$_FILES['import']['name'])
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please specify a file to import'));
			$this->_redirect('*/*/import');
			return;
		}

		if($_FILES['import']['type'] != 'text/csv' && $_FILES['import']['type'] != 'application/vnd.ms-excel')
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Only files of type text/csv are allowed'));
			$this->_redirect('*/*/import');
			return;
		}
		
		$imported = Mage::getModel('devils_colors/adminhtml_import')->import($_FILES['import']['tmp_name'], $this->getRequest()->getParam('first_row', false));
		if($imported['success'] == true)
		{
			Mage::getSingleton('adminhtml/session')->addSuccess($imported['message']);
			$this->_redirect('*/*/index');
		}else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__($imported['message']));
			$this->_redirect('*/*/import');
		}
	}
	
	public function massDeleteAction()
	{
		$ids = $this->getRequest()->getParam('devils_colors');
		if(!is_array($ids)){
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
		}else{
			try
			{
				foreach ($ids as $id)
				{
					$color = Mage::getModel('devils_colors/color')->load($id);
					$color->delete();

					$colorPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'devils' . DS . 'devils_colors' . DS . 'colors' . DS . $id;
					$cachePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'devils' . DS . 'devils_colors' . DS . 'cache' . DS . $id;
					$this->_clearDir($colorPath);
					$this->_clearDir($cachePath);
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('devils_colors')->__('Total of %d colors were successfully deleted', count($ids)));
			}catch(Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	protected function _clearDir($dir)
	{
		if(file_exists($dir)){
			$glob = glob($dir . '/*');
			if($glob){
				foreach($glob as $file){
					if(is_dir($file)){
						$this->_clearDir($file);
					}else{
						unlink($file);
					}
				}
				rmdir($dir);
			}
		}
	}
}
?>