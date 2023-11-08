<?php
class Looship_Admin_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $isEnabled = Mage::getStoreConfig('shipping/looship_admin/enabled');
        if ($isEnabled) {
            $looid = $this->getRequest()->getParam('looid');
            if ($looid) {
                Mage::getSingleton('core/session')->setLooid($looid);
            }
        }
    }
}
?>