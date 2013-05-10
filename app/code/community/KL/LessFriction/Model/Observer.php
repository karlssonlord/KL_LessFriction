<?php
class KL_LessFriction_Model_Observer
{

    const LESSFRICTION_FREESHIPPING = 'freeshipping';

    var $_defaultCountry = null;

    public function preDispatch(Varien_Event_Observer $observer) {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $_config = Mage::getSingleton('lessfriction/config');

        /* @var $address Mage_Sales_Model_Quote_Address */
        $billingAddress  = $quote->getBillingAddress();

        /* @var $address Mage_Sales_Model_Quote_Address */
        $shippingAddress = $quote->getShippingAddress();

        if ($billingAddress) {
            if (!$billingAddress->getCountryId()) {
                $billingAddress->setCountryId($this->_getDefaultCountry()->getCountryId())->save();
            }

            $store = $quote ? $quote->getStoreId() : null;
            $methods = Mage::helper('payment')->getStoreMethods($store, $quote);
            $total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();

            foreach ($methods as $key => $method) {
                if ($this->_canUsePaymentMethod($method)
                    && ($total != 0
                        || $method->getCode() == 'free'
                        || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))) {
                    $availablePaymentMethods[] = $method->getCode();
                } else {
                    unset($methods[$key]);
                }
            }

            if (count($availablePaymentMethods) == 1) {
                Mage::getModel('checkout/type_onepage')
                    ->savePayment(array('method' => current($availablePaymentMethods)));
            }
        }

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
            if (
                $_config->preselectSingleShippingMethod() ||
                $_config->preselectCheapestShippingMethod()
            ) {
                $groups = $shippingAddress->getGroupedAllShippingRates();

                if (!empty($groups)) {
                    foreach ($groups as $code => $groupItems) {
                        if (!$_config->preselectCheapestShippingMethod()) {
                            if ($code == self::LESSFRICTION_FREESHIPPING) {
                                if (count($groupItems) == 1) {
                                    $shippingMethod = $groupItems[0];
                                    $shippingAddress->setShippingMethod($shippingMethod->getCode());
                                }
                            } else if (count($groups) == 1 && count($groupItems) == 1) {
                                $shippingMethod = $groupItems[0];
                                $shippingAddress->setShippingMethod($shippingMethod->getCode());
                            }
                        } else {
                            foreach ($groupItems as $item) {
                                if (!isset($cheapestShippingMethod) || $cheapestShippingMethod->getPrice() > $item->getPrice()) {
                                    $cheapestShippingMethod = $item;
                                }
                            }
                        }
                    }

                    if ($_config->preselectCheapestShippingMethod() && isset($cheapestShippingMethod)) {
                        $shippingAddress->setShippingMethod($cheapestShippingMethod->getCode());                        
                    }
                }
            }
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

    protected function _canUsePaymentMethod($method)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        if (!$method->canUseForCountry($quote->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency($quote->getStore()->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = $quote->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }

        return true;
    }
}
