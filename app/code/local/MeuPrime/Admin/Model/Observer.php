<?php
class MeuPrime_Admin_Model_Observer
{
    public function captureLooid($observer)
    {
        $isEnabled = Mage::getStoreConfig('shipping/meuprime_admin/enabled');
        if (!$isEnabled) return;
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        Mage::helper('meuprime_admin')->setLooData($request);
    }

    public function orderConfirmed(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $order_status = Mage::getStoreConfig('shipping/meuprime_admin/order_status');
        if ($order->getStatus() == $order_status) return;
        
        $isEnabled = Mage::getStoreConfig('shipping/meuprime_admin/enabled');
        if (!$isEnabled) return;

        $external_id = $order->getIncrementId();
        $loo_job_id = Mage::getSingleton('core/session')->getLoojobid();
        if (!isset($loo_job_id)) return;
        
        $url = Mage::helper('meuprime_admin')->getHost() . '/v3/order/' . $loo_job_id . '/confirmed';
        $postData = array(
            "external_id" => $external_id,
        );
        $newData = Mage::helper('meuprime_admin')->postRequest($url, $postData);
        if (isset($newData) && $newData['valid'] == true)
        {
            Mage::getSingleton('core/session')->unsLooJobId();
            Mage::getSingleton('core/session')->unsLooId();
        }
    }

    public function shippingQuote(Varien_Event_Observer $observer)
    {
        $isEnabled = Mage::getStoreConfig('shipping/meuprime_admin/enabled');
        if (!$isEnabled) return; 
        $voucher = Mage::helper('meuprime_admin')->getLooId();
        if (!isset($voucher) || $voucher == '') return;

        $store = Mage::app()->getStore();
        $shippingOrigin = $store->getConfig('shipping/origin/postcode');
        $quote = $observer->getQuote();        
        if (!$quote) return;

        $shippingAddress = $quote->getShippingAddress();
        $shippingRates = $shippingAddress->getAllShippingRates();
        $freights = Mage::helper('meuprime_admin')->getShippingRates($shippingRates);
        if (count($freights) <= 0) return;

        $items = Mage::helper('meuprime_admin')->getOrderItems();                        
        if (count($items) <= 0) return;
    
        $checkoutSession = Mage::getSingleton('checkout/session');
        $simulate = $checkoutSession->getQuote() && $checkoutSession->getLastRealOrderId();
        $media = Mage::helper('meuprime_admin')->getLooMedia();
        $destinationZipCode = $shippingAddress->getPostcode();

        $postData = array(
            "simulate"  => $simulate,
            "media"     => $media,
            "voucher"   => $voucher,
            "warehouse" => array(
                "zipcode" => $shippingOrigin,
            ),
            "shipping"  => array(
                "zipcode" => $destinationZipCode,
            ),
            "items"     => $items,
            "freights"  => $freights,
        );

        $url = Mage::helper('meuprime_admin')->getHost() . '/v3/voucher/validate';
        $newData = Mage::helper('meuprime_admin')->postRequest($url, $postData);
        if (isset($newData) && $newData['valid'] == true)
        {
            $newFreights = $newData['freights'];
            if (isset($newFreights) && is_array($newFreights))
            {
                Mage::getSingleton('core/session')->setLoojobid($newData['job_id']);
                foreach ($shippingAddress->getShippingRatesCollection() as $shippingRate) 
                {
                    $targetShippingRateLabel = $shippingRate->getCode(); 
                    foreach ($newFreights as $freight) 
                    {
                        if ($freight['label'] == $targetShippingRateLabel) 
                        {
                            $newRatePrice = (float) $freight['value'];
                            $shippingRate->setPrice($newRatePrice);
                            $shippingRate->save();
                            break;
                        }
                    }
                }
            }
        }
    }
}
?>