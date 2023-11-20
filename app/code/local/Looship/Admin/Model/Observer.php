<?php
class Looship_Admin_Model_Observer
{
    public function captureLooid($observer)
    {
        $isEnabled = Mage::getStoreConfig('shipping/looship_admin/enabled');
        if (!$isEnabled) return;
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        $looid = $request->getParam('looid');
        if (!$looid || $looid == '') return;

        $reference = Mage::app()->getRequest()->getHeader('referer') || Mage::app()->getRequest()->getServer('HTTP_REFERER');
        $media = array(
            "reference"    => $reference,
            "utm_source"   => $request->getParam('utm_source'),
            "utm_campaign" => $request->getParam('utm_campaign'),
            "utm_medium"   => $request->getParam('utm_medium'),
        );
        Mage::getSingleton('core/session')->setLooId($looid);
        Mage::getSingleton('core/session')->setLooMedia($media);
    }

    public function orderConfirmed(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE) return;
        
        $isEnabled = Mage::getStoreConfig('shipping/looship_admin/enabled');
        if (!$isEnabled) return;

        $orderNumber = $order->getIncrementId();
        $job_id = Mage::getSingleton('core/session')->getLooJobid();
        $url = Mage::helper('looship_admin')->getHost() . '/v3/order/' . $orderNumber . '/confirmed';
        $postData = array(
            "job_id" => $job_id,
        );                
        $newData = Mage::helper('looship_admin')->postRequest($url, $postData);                
    }

    public function shippingQuote(Varien_Event_Observer $observer)
    {
        $isEnabled = Mage::getStoreConfig('shipping/looship_admin/enabled');
        if (!$isEnabled) return; 
        $voucher = Mage::helper('looship_admin')->getLooId();
        if ($voucher == '') return;

        $store = Mage::app()->getStore();
        $shippingOrigin = $store->getConfig('shipping/origin/postcode');
        $quote = $observer->getQuote();        
        if (!$quote) return;

        $shippingAddress = $quote->getShippingAddress();
        $shippingRates = $shippingAddress->getAllShippingRates();
        $freights = Mage::helper('looship_admin')->getShippingRates($shippingRates);
        if (count($freights) <= 0) return;

        $items = Mage::helper('looship_admin')->getOrderItems();                        
        if (count($items) <= 0) return;
    
        $checkoutSession = Mage::getSingleton('checkout/session');
        $simulate = $checkoutSession->getQuote() && $checkoutSession->getLastRealOrderId();
        $media = Mage::helper('looship_admin')->getLooMedia();
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

        $url = Mage::helper('looship_admin')->getHost() . '/v3/voucher/validate';
        $newData = Mage::helper('looship_admin')->postRequest($url, $postData);
        if (isset($newData) && $newData['valid'] == true)
        {
            $newFreights = $newData['freights'];
            if (isset($newFreights) && is_array($newFreights))
            {
                Mage::getSingleton('core/session')->setLooJobid($newData['job_id']);
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