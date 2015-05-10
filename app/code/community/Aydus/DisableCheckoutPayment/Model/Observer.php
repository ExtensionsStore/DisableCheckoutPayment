<?php

/**
 * Disable checkout observer
 *
 * @category   Aydus
 * @package    Aydus_DisableCheckoutPayment
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_DisableCheckoutPayment_Model_Observer 
{
    /**
     * Add disable checkout field to all payment groups
     *
     * @see adminhtml_init_system_config
     * @param Mage_Core_Model_Observer $observer
     * @return Varien_Event_Observer
     */
    public function addDisableCheckoutPaymentFields($observer)
    {
        $config = $observer->getConfig();
    
        $paymentGroups = $config->getNode('sections/payment/groups');
        
        //position field after title or enabled field
        $usualPaymentGroups = array(
                'ccsave' => 2,
                'checkmo' => 2,
                'free' => 2,
                'purchaseorder' => 2,
                'banktransfer' => 15,
                'cashondelivery' => 15,
                'authorizenet' => 2,
                'authorizenet_directpost' => 15,
        );        
        
        $methods = array_keys((array)$paymentGroups);
        
        foreach ($usualPaymentGroups as $method => $position){
            
            if (!in_array($method, $methods)){
                $observer->setAddedDisableCheckoutPaymentFields(false);
                return $observer;
            }
        }

        $usualPaymentGroupsKeys = array_keys($usualPaymentGroups);

        foreach ($paymentGroups->children() as $group => $element){

            $fields = &$element->fields;

            if (!$fields->disable_checkout){

                $disableCheckout = $fields->addChild('disable_checkout');

                $disableCheckout->addAttribute('translate', 'label');
                $disableCheckout->addAttribute('module', 'aydus_disablecheckoutpayment');

                $disableCheckout->addChild('label', 'Disable Checkout');
                $disableCheckout->addChild('frontend_type', 'select');
                $disableCheckout->addChild('source_model', 'adminhtml/system_config_source_yesno');
                $sortOrder = (in_array($group, $usualPaymentGroupsKeys)) ? $usualPaymentGroups[$group] : 500;
                $disableCheckout->addChild('sort_order', $sortOrder);
                $disableCheckout->addChild('show_in_default', 1);
                $disableCheckout->addChild('show_in_website', 1);
                $disableCheckout->addChild('show_in_store', 0);
                $disableCheckout->addChild('comment', 'Disable front end checkout.');

            }

        }       
        
        $observer->setAddedDisableCheckoutPaymentFields(true);
            
        return $observer;
    }
        
    /**
     * 
     * Disable payment method on front end if set as disabled
     * 
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer
     */
    public function disableCheckout($observer)
    {
        $method = $observer->getMethodInstance();
        $code = $method->getCode();
        $result = $observer->getResult();
        
        $storeId = Mage::app()->getStore()->getId();
        
        $disableCheckout = Mage::getStoreConfig("payment/$code/disable_checkout", $storeId);
                
        if ($disableCheckout){
            
            $result->isAvailable = false;
            
            $observer->setResult($result);
            
        }
        
        return $observer;
    }
   
}