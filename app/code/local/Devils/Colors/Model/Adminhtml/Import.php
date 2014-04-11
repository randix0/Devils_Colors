<?php
class Devils_Colors_Model_Adminhtml_Import extends Mage_Core_Model_Abstract
{
    protected $_inserted;
    protected $_modified;
    protected $_processed;

    public function __construct()
    {
        $this->_inserted = 0;
        $this->_modified = 0;
        $this->_processed = 0;
        Mage::app(Mage_Core_Model_Store::ADMIN_CODE);
    }

    public function import($csv, $firstRow = false)
    {
        try
        {
            $invalidCode = false;
            $invalidProduct = false;
            ini_set('auto_detect_line_endings', true);
            if(($fp = fopen($csv, 'r')) === false)
            {
                return array('success' => false, 'message' => 'Unable to open import file.');
            }

            /*
            Default row array indexes for each column.
            If the user has specified these themselves, map the indexes below
            */
            $columnIndex = array(
                'name' => 0,
                'color' => 1,
                'image' => 2,
                'attribute_code' => 3,
                'sku' => 4
            );

            if($firstRow !== false){
                $columnIndex = $this->_mapCsv(fgetcsv($fp), $columnIndex);
            }

            while($row = fgetcsv($fp))
            {
                if($row[$columnIndex['name']] !== null)
                {
                    $data = new Varien_Object();
                    $data->setName($row[$columnIndex['name']]);
                    $data->setValue($row[$columnIndex['color']]);
                    if(isset($row[$columnIndex['image']]))
                    {
                        $image = $row[$columnIndex['image']];
                        if(substr($image, 0, 1) == '/'){
                            $image = substr($image, 1);
                        }
                        $data->setFile($image);
                    }

                    $color = $this->_getColor($data->getName());
                    if(!$color->getId()){
                        $color->setData($data->getData())->save();
                        if($color->getId()){
                            $this->_copyImage($data->getFile(), $color->getId());
                            $this->_inserted++;
                        }
                    }else{
                        $color->setValue($data->getValue())
                            ->setFile($data->getFile())
                            ->save();

                        $this->_copyImage($data->getFile(), $color->getId());
                        $this->_modified++;
                    }

                    if(isset($row[$columnIndex['attribute_code']]) && trim($row[$columnIndex['attribute_code']]) != ''){
                        if(stristr($row[$columnIndex['attribute_code']], 'color') !== false){
                            if(($option = $this->_addOption($color->getName(), $row[$columnIndex['attribute_code']])) !== false){
                                Mage::getModel('devils_colors/attribute')
                                    ->setAttributeId($option['attribute_id'])
                                    ->setOptionId($option['option_id'])
                                    ->setColorId($color->getId())
                                    ->save();

                                if(isset($row[$columnIndex['sku']]) && trim($row[$columnIndex['sku']]) != ''){
                                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', trim($row[$columnIndex['sku']]));
                                    if($product && $product->getId()){
                                        $product->setData($row[$columnIndex['attribute_code']], $option['option_id'])->save();
                                    }else{
                                        Mage::log(sprintf('Config Grid - Unable to locate product with SKU matching "%s" for updating.', trim($row[$columnIndex['sku']])));
                                        $invalidProduct = true;
                                    }
                                }
                            }
                        }else{
                            $invalidCode = true;
                        }
                    }
                    $this->_processed++;
                }
            }

            fclose($fp);

            if($invalidCode){
                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('devils_colors')->__('It is not possible to assign a color swatch to a non-color attribute.'));
            }

            if($invalidProduct){
                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('devils_colors')->__('One or more products could not be updated. Please check the Magento log file for details.'));
            }

            return array('success' => true, 'message' => sprintf('Import successful!<br /><br />%s new swatches added.<br />%s swatches updated.<br />%s records processed.', $this->_inserted, $this->_modified, $this->_processed));
        }catch(Exception $e){
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    protected function _mapCsv($row, $originalIndex)
    {
        $columns = $originalIndex;
        for($i=0; $i<count($row); $i++){
            if(trim($row[$i]) == 'name' && !array_key_exists('name', $columns)){
                $columns['name'] = $i;
            }

            if(trim($row[$i]) == 'color' && !array_key_exists('color', $columns)){
                $columns['color'] = $i;
            }

            if(trim($row[$i]) == 'image' && !array_key_exists('image', $columns)){
                $columns['image'] = $i;
            }

            if(trim($row[$i]) == 'attribute_code' && !array_key_exists('attribute_code', $columns)){
                $columns['attribute_code'] = $i;
            }

            if(trim($row[$i]) == 'sku' && !array_key_exists('sku', $columns)){
                $columns['sku'] = $i;
            }
        }

        if(!array_key_exists('name', $columns)
            || !array_key_exists('color', $columns)){
            throw new Exception('One or more required column names are unspecified in the import file. Please specify at least the name and color columns.');
        }
        return $columns;
    }

    protected function _copyImage($filename, $id)
    {
        if(trim($filename) == ''){
            return;
        }

        $path = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'devils' . DS . 'devils_colors' . DS . 'colors' . DS . $id;
        if(!file_exists($path)){
            $created = mkdir($path, 0777, true);
            if(!$created){
                throw new Exception('Failed to create the directory path for storing images.<br />Please make sure your PHP version is at least 5.0.0 and that it has permissions to create directories.');
            }
        }
        $from = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'import' . DS . $filename;
        $to = $path . DS . $filename;

        if(!copy($from, $to)){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('devils_colors')->__('Unable to copy %s to the corresponding directory. Please check file/folder permissions and ensure the file exists.', $filename));
        }
    }

    protected function _addOption($name, $code)
    {
        $model = Mage::getModel('eav/entity_attribute');
        $attrId = $model->getIdByCode('catalog_product', $code);
        $attribute = $model->load($attrId);

        if($this->_getOptionValue($code, $name) === false){
            $value['option'] = array($name, $name);
            $attribute->setData('option', array('value' => $value));
            $attribute->save();
        }
        return $this->_getOptionValue($code, $name);
    }

    public function _getOptionValue($code, $label)
    {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $code);
        foreach($attribute->getSource()->getAllOptions(true, true) as $option){
            if($option['label'] == $label){
                return array('option_id' => $option['value'], 'attribute_id' => $attribute->getAttributeId());
            }
        }
        return false;
    }

    protected function _getColor($name)
    {
        return Mage::getModel('devils_colors/color')->loadByName($name);
    }
}
