<?php
class Devils_Colors_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function cleanArray(array $arr)
	{
		return array_filter($arr, array($this, '_clean'));
	}

	public function translateLabel($label)
	{
		$outputLabel = str_ireplace(array('Extra', 'Small', 'Medium', 'Large', '-', ' '), array('X', 'S', 'M', 'L', '', ''), $label);
		if($outputLabel !== $label){
			return strtoupper($outputLabel);
		}
		return $outputLabel;
	}

	public function canTranslate($attrCode)
	{
		if(stristr($attrCode, 'size') === false){
			return false;
		}
		return (bool)Mage::getStoreConfig('devils_colors/general/translate');
	}

	public function getSwatchImage($id, $file)
	{
		$display = Mage::getStoreConfig('devils_colors/general/display');
		$path = '/media/devils/devils_colors/colors/%s/%s';
		if((int)$display === 0){
			return $this->_getResizedImage($file, $id, 47, 40);
		}
		return sprintf($path, $id, $file);
	}

	protected function _getResizedImage($filename, $dir, $width, $height = null)
	{
		$imagePath = Mage::getBaseDir('media') . DS . 'devils' . DS . 'devils_colors' . DS . 'colors' . DS . $dir . DS . $filename;
        $imageResized = Mage::getBaseDir('media') . DS . 'devils' . DS . 'devils_colors' . DS . 'cache' . DS . $dir . DS . $width . '_' . (string)$height . '_' . $filename;
        if(!file_exists($imageResized) && file_exists($imagePath) || file_exists($imagePath) && filemtime($imagePath) > filemtime($imageResized)){
            $imageObj = new Varien_Image($imagePath);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepFrame(false);
            $imageObj->quality(100);
            $imageObj->resize($width, $height);
            $imageObj->save($imageResized);
        }

        $imageCacheUrl = '/media/devils/devils_colors/cache/' . $dir . '/' . $width . '_' . (string)$height . '_' . $filename;
        
        if(file_exists($imageResized)){
            return $imageCacheUrl;
        }
        return '';
    }

    public function getShowNotice()
    {
    	return (bool)Mage::getStoreConfig('devils_colors/general/show_notice');
    }

    public function getNoticeMessage()
    {
    	return Mage::getStoreConfig('devils_colors/general/notice_msg');
    }

	public function getStockNoticeLevel()
	{
		return (int)Mage::getStoreConfig('devils_colors/general/notice');
	}

	protected function _clean($var)
	{
		$notEmpty = false;
		foreach($var as $val){
			if(!empty($val)){
				$notEmpty = true;
				break;
			}
		}
		return $notEmpty;
	}
}