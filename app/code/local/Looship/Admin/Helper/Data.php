<?php
class Looship_Admin_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getLooid()
    {
        return Mage::getSingleton('core/session')->getLooid();
    }
}
