<?php
class Looship_Admin_Model_Observer
{
    public function captureLooid($observer)
    {
        $isEnabled = Mage::getStoreConfig('shipping/looship_admin/enabled');
        if ($isEnabled) {
            $request = $observer->getEvent()->getControllerAction()->getRequest();
            $looid = $request->getParam('looid');
            if ($looid) {
                Mage::getSingleton('core/session')->setLooid($looid);
            }
        }
    }

    public function quoteShipping(Varien_Event_Observer $observer)
    {
        $isEnabled = Mage::getStoreConfig('shipping/looship_admin/enabled');
        if ($isEnabled) 
        {
            $voucher = Mage::helper('looship_admin')->getLooid();

            if ($voucher !== '') 
            {
                $store = Mage::app()->getStore();
                $shippingOrigin = $store->getConfig('shipping/origin/postcode');
                $quote = $observer->getQuote();        
                $shippingRates = [];
                $destinationZipCode = null;
                if ($quote !== null) {
                    $shippingAddress = $quote->getShippingAddress();
                    $destinationZipCode = $shippingAddress->getPostcode();
                    $shippingRates = $shippingAddress->getAllShippingRates();
                }

                $items = [];
                $cart = Mage::getModel('checkout/cart');
                $cart_items = $cart->getQuote()->getAllItems();
                foreach ($cart_items as $item) 
                {
                    $productId = $item->getProductId();
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $items[] = array(
                        "sku"      => $product->getSku(),
                        "name"     => $product->getName(),
                        "amount"   => $item->getQty(),
                        "subtotal" => $item->getPrice(),
                        "taxonomy" => $product->getAttributeText('taxonomy'),
                        "weight"   => (float) $product->getWeight(),
                    );
                }

                $freights = [];
                foreach ($shippingRates as $rate) 
                {
                    if ($rate !== null) {
                        $freights[] = array(
                            "label" => $rate->getCode(),
                            "time"  => (int) $rate->getTimeInTransit(),
                            "value" => $rate->getPrice(),
                        );
                    }
                }

                $postData = array(
                    "simulate" => false,
                    "media"    => array(
                        "reference"    => "site",
                        "utm_source"   => "google",
                        "utm_campaign" => "musical",
                        "utm_medium"   => "site"
                    ),
                    "voucher"  => $voucher,
                    "warehouse" => array(
                        "zipcode" => $shippingOrigin,
                    ),
                    "shipping" => array(
                        "zipcode" => $destinationZipCode,
                    ),
                    "items"    => $items,
                    "freights" => $freights,
                );

                $jsonData = json_encode($postData);

                $client = new Varien_Http_Client();
                $url = 'https://api.looship.com.br/v3/voucher/validate';
                $token = Mage::getStoreConfig('shipping/looship_admin/token');
                $headers = array(
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Length' => strlen($jsonData),
                    'Content-Type' => 'application/json',
                );
                try {
                    $client->setHeaders($headers);
                    $client->setUri($url)
                        ->setConfig(array('maxredirects' => 0, 'timeout' => 300))
                        ->setMethod(Zend_Http_Client::POST)
                        ->setRawData($jsonData, 'application/json');
                    $response = $client->request();
                    Mage::log($jsonData, null, 'looship.log');
                    if ($response->isSuccessful())
                    {
                        $body = $response->getBody();
                        $responseData = json_decode($body, true);
                        $newData = $responseData['data'];
                        if ($newData['valid'] == true)
                        {
                            Mage::getSingleton('core/session')->setJobid($newData['job_id']);
                            $newFreights = $newData['freights'];
                            if (isset($newFreights) && is_array($newFreights))
                            {
                                foreach ($shippingRates as $shippingRate) 
                                {
                                    $targetShippingRateLabel = $shippingRate->getCode(); 
                                    foreach ($newFreights as $freight) 
                                    {
                                        if ($freight['label'] == $targetShippingRateLabel) 
                                        {
                                            $newRatePrice = $freight['value'];
                                            $shippingRate->setPrice($newRatePrice);
                                            $shippingRate->save();
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    Mage::log(json_encode($e->getMessage()), null, 'request.log');
                }
            }
            
        }
    }
}
