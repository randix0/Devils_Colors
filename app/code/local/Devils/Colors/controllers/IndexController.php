<?php
class Devils_Colors_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Register the logged in customer for a product stock alert
     * Response is JSON encoded string containing a boolean success and message
     */
	public function stockAction()
	{
		$productId = (int)$this->getRequest()->getParam('product_id');
		$configProduct = Mage::getModel('catalog/product')->load($productId);

		if(!$configProduct){
			$this->getResponse()->setBody(
            	Mage::helper('core')->jsonEncode(array('success' => false, 'message' => $this->__('Sorry, the requested product is not available for purchase.')))
            );
            return;
		}

        $collection = Mage::getModel('catalog/product_type_configurable')
			->getUsedProductCollection($configProduct)
			->addAttributeToSelect('name');

        foreach($this->getRequest()->getParams() as $superId => $optionId){
        	if($superId != 'product_id'){
        		$code = $this->_getAttributeCode($superId);
        		$collection->addAttributeToSelect($code)
        			->addAttributeToFilter($code, array('eq' => $optionId));
        	}
        }

        if(!$collection->getSize()){
        	$this->getResponse()->setBody(
            	Mage::helper('core')->jsonEncode(array('success' => false, 'message' => $this->__('Sorry, the requested product is not available for purchase.')))
            );
            return;
        }

        $product = $collection->getFirstItem();

        try
        {
            $model = Mage::getModel('productalert/stock')
                ->setCustomerId(Mage::getSingleton('customer/session')->getId())
                ->setProductId($product->getId())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
            $model->save();

            $this->getResponse()->setBody(
            	Mage::helper('core')->jsonEncode(array('success' => true, 'message' => $this->__('Thank you! You will receive an email notification when the selected product comes back in stock.')))
            );
        }catch(Exception $e){
        	$this->getResponse()->setBody(
            	Mage::helper('core')->jsonEncode(array('success' => false, 'message' => $this->__('You are already subscribed to this product alert. ' . $e->getMessage())))
            );
        }
	}

    /**
     * Attempt to log user in to Magento
     * Response is JSON encoded string containing a boolean success and message
     */
	public function loginAction()
	{
        $session = $this->_getSession();
        $response = array();

        if ($this->getRequest()->isPost()){
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])){
                try {
                    $session->login($login['username'], $login['password']);
                    $response = array('success' => true, 'message' => '');
                }catch(Mage_Core_Exception $e){
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $response = array('success' => false, 'message' => $message);
                    $session->setUsername($login['username']);
                }catch(Exception $e){
                }
            } else {
            	$response = array('success' => false, 'message' => $this->__('Login and password are required.'));
            }
        }
        $this->getResponse()->setBody(
        	Mage::helper('core')->jsonEncode($response)
        );
	}

    /**
     * Retrieve instance of customer session
     *
     * @return Mage_Customer_Model_Session
     */
	protected function _getSession()
	{
		return Mage::getSingleton('customer/session');
	}

    /**
     * Retrieve attribute code based on given attribute id
     *
     * @param int $attributeId
     * @return string
     */
	protected function _getAttributeCode($attributeId)
	{
		return Mage::getModel('eav/entity_attribute')
			->load($attributeId)
			->getAttributeCode();
	}
}