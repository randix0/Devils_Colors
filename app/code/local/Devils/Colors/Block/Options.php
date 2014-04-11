<?php

class Devils_Colors_Block_Options extends Mage_Core_Block_Template
{
    public function __construct()
    {
        $this->setTemplate('devils/colors/options.phtml');
    }

    public function getConfigStyle()
    {
        $style = array();
        if(!$this->getSuperAttribute()->decoratedIsFirst){
            $style[] = 'disabled';
        }

        return implode(' ', $style);
    }

    public function getGridClass()
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '-', $this->getSuperAttribute()->getLabel()));
    }
}