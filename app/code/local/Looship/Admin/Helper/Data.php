<?php
class Looship_Admin_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getHost()
    {
        return 'https://api.looship.com.br';
    }

    public function getLooId()
    {
        return Mage::getSingleton('core/session')->getLooId();
    }

    public function getLooMedia()
    {
        $media = Mage::getSingleton('core/session')->getLooMedia();
        if (!$media) {
            return array(
                "reference"    => Mage::app()->getRequest()->getServer('HTTP_REFERER'),
                "utm_source"   => "none",
                "utm_campaign" => "none",
                "utm_medium"   => "site"
            );
        }
        return $media;
    }

    public function getOrderItems()
    {
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

        return $items;
    }

    public function getShippingRates($shippingRates)
    {
        $freights = [];
        if (isset($shippingRates) && is_array($shippingRates))
        {
            foreach ($shippingRates as $rate) 
            {
                if ($rate) 
                {
                    $freights[] = array(
                        "label" => $rate->getCode(),
                        "time"  => (int) $rate->getTimeInTransit(),
                        "value" => $rate->getPrice(),
                    );
                }
            }
        }
        return $freights;
    }

    public function postRequest($url, $postData)
    {
        try 
        {
            $token = Mage::getStoreConfig('shipping/looship_admin/token');
            if ($token !== '')
            {
                $jsonData = json_encode($postData);
                Mage::log($jsonData, null, 'looship_debug.log');
                $headers = array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonData),
                    'Authorization: Bearer ' . $token,
                );

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $response = curl_exec($ch);
                $curl_error = curl_error($ch);
                if ($curl_error) throw new Exception($curl_error);
                curl_close($ch);

                Mage::log($response, null, 'looship_debug.log');
                $responseData = json_decode($response, true);
                $newData = $responseData['data'];
                return $newData;
            }
            return [];
        } catch (Exception $e) {
            Mage::log(json_encode($e->getMessage()), null, 'looship_request.log');
        }
    }
}
?>