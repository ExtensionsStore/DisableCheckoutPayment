<?php

/**
 * Disable checkout observer test
 *
 * @category   Aydus
 * @package    Aydus_DisableCheckoutPayment
 * @author     Aydus <davidt@aydus.com>
 */

class Aydus_DisableCheckoutPayment_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case_Config
{    
    /**
     * Test adding fields to configuration
     *
     * @test
     */
    public function addDisableCheckoutPaymentFieldsTest()
    {
        $this->assertEventObserverDefined(
         'adminhtml', 'adminhtml_init_system_config', 'aydus_disablecheckoutpayment/observer', 'addDisableCheckoutPaymentFields'
        );

        $config = Mage::getConfig()->loadModulesConfiguration('system.xml')
            ->applyExtends();
        $observer = new Varien_Event_Observer();
        $observer->setConfig($config);

        $model = Mage::getModel('aydus_disablecheckoutpayment/observer');
                        
        $observer = $model->addDisableCheckoutPaymentFields($observer);

        $this->assertTrue($observer->getAddedDisableCheckoutPaymentFields());            

    }
        
    /**
     * Test disabling checkout payment
     *
     * @test
     * @loadFixture
     */
    public function disableCheckoutTest()
    {
        $this->assertEventObserverDefined(
         'frontend', 'payment_method_is_active', 'aydus_disablecheckoutpayment/observer', 'disableCheckout'
        );
               
        $checkMo = Mage::getModel('payment/method_checkmo');
        $result = $checkMo->isAvailable();
        $this->assertEventDispatched('payment_method_is_active');
        
        $model = Mage::getModel('aydus_disablecheckoutpayment/observer');
        $observer = new Varien_Event_Observer();
        $observer->setMethodInstance($checkMo);
        $checkResult = new StdClass;
        $checkResult->isAvailable = true;
        $observer->setResult($checkResult);
        
        $model->disableCheckout($observer);
                                
        $this->assertFalse($checkResult->isAvailable);

    }
   
}