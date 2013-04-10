<?php
class KL_LessFriction_Model_Observer
{
    public function preDispatch(Varien_Event_Observer $observer) {
        $quote = Mage::getSingleton('checkout/session');

        /* @var $address Mage_Sales_Model_Quote_Address */
        $shippingAddress = $quote->getShippingAddress();

        /* @var $address Mage_Sales_Model_Quote_Address */
        $billingAddress  = $quote->getBillingAddress();

        if ($shippingAddress) {
            if (!$shippingAddress->getCountryId() || $shippingAddress->getShippingRatesCollection()->count() == 1) {
                $shippingAddress->setCountryId($this->_getDefaultCountry()->getId())
                        ->setCollectShippingRates(true)
                        ->collectShippingRates()
                        ->save();
            } else if ($shippingAddress->getCountryId()) {
                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->save();
            }
        }

        if ($billingAddress && !$billingAddress->getCountryId()) {
            $billingAddress->setCountryId($this->_getDefaultCountry()->getId())->save();
        }
    }

    protected function _getDefaultCountry()
    {
        if (!$this->_defaultCountry) {
            $countryId = Mage::registry('client_country_id') ?
                            Mage::registry('client_country_id') :
                            Mage::helper('core')->getDefaultCountry();

            $this->_defaultCountry = new Varien_Object(array('country_id' => $countryId));
        }

        return $this->_defaultCountry;
    }
}
