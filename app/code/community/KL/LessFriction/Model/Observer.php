<?php
class KL_LessFriction_Model_Observer
{
    var $_defaultCountry = null;

    public function preDispatch(Varien_Event_Observer $observer) {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        /* @var $address Mage_Sales_Model_Quote_Address */
        $shippingAddress = $quote->getShippingAddress();

        /* @var $address Mage_Sales_Model_Quote_Address */
        $billingAddress  = $quote->getBillingAddress();

        if ($shippingAddress) {
            if (!$shippingAddress->getCountryId() || $shippingAddress->getShippingRatesCollection()->count() == 1) {
                $shippingAddress->setCountryId($this->_getDefaultCountry()->getCountryId())
                        ->setCollectShippingRates(true)
                        ->collectShippingRates()
                        ->save();
            } else if ($shippingAddress->getCountryId()) {
                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->save();
            }

            /**
             * If there's only one shipping method available we might want to preselect
             * it based on how the module is configured in admin
             **/
            if (Mage::getModel('lessfriction/config')->preselectSingleShippingMethod()) {
                $groups = $shippingAddress->getGroupedAllShippingRates();

                if (!empty($groups)) {
                    foreach ($groups as $code => $groupItems) {
                        if (count($groups) == 1 && count($groupItems) == 1) {
                            $shippingMethod = $groupItems[0];
                            $shippingAddress->setShippingMethod($shippingMethod->getCode());
                        }
                    }
                }
            }
        }

        if ($billingAddress && !$billingAddress->getCountryId()) {
            $billingAddress->setCountryId($this->_getDefaultCountry()->getCountryId())->save();
        }
    }

    protected function _getDefaultCountry()
    {
        if ($this->_defaultCountry === null) {
            $countryId = Mage::registry('client_country_id') ?
                            Mage::registry('client_country_id') :
                            Mage::helper('core')->getDefaultCountry();

            $this->_defaultCountry = new Varien_Object(array('country_id' => $countryId));
        }

        return $this->_defaultCountry;
    }
}
