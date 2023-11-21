<?php
class Looship_Admin_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $isEnabled = Mage::getStoreConfig('shipping/looship_admin/enabled');
        if (!$isEnabled) return;
        $looid = $this->getRequest()->getParam('looid');
        if (!isset($looid) || $looid == '') return;
        Mage::getSingleton('core/session')->setLooId($looid);
    }
}
?>