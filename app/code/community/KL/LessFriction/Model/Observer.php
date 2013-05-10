<?php
class KL_LessFriction_Model_Observer
{

    const LESSFRICTION_FREESHIPPING = 'freeshipping';

    var $_defaultCountry = null;

    /**
     * Pre dispatch
     *
     * @return void
     */
    public function preDispatch(Varien_Event_Observer $observer) {
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $this->_getQuote();

        /* @var $address Mage_Sales_Model_Quote_Address */
        $billingAddress  = $quote->getBillingAddress();

        /* @var $address Mage_Sales_Model_Quote_Address */
        $shippingAddress = $quote->getShippingAddress();

        if ($billingAddress) {
            if (!$billingAddress->getCountryId()) {
                $billingAddress->setCountryId($this->_getDefaultCountry()->getCountryId())->save();
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
        }

        if ($billingAddress && $shippingAddress) {
            /**
             * Try setting payment method
             **/
            $this->_setPaymentMethod();

            /**
             * Try setting shipping method
             **/
            $this->_setShippingMethod();
        }
    }

    /**
     * Get default country
     *
     * @return object
     */
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

    /**
     * Try setting payment method based on configuration
     *
     * @return void
     */
    protected function _setPaymentMethod()
    {
        $config = $this->_getConfig();

        if ($config->preselectSinglePaymentMethod() === true) {
            $quote   = $this->_getQuote();
            $store   = $quote ? $quote->getStoreId() : null;
            $methods = Mage::helper('payment')->getStoreMethods($store, $quote);
            $total   = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();

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
    }

    /**
     * Check if customer can use payment method
     *
     * @param object
     *
     * @return bool
     */
    protected function _canUsePaymentMethod($method)
    {
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $this->_getQuote();

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

    /**
     * Try setting shipping method based on configuration
     *
     * @return void
     */
    protected function _setShippingMethod()
    {
        $config = $this->_getConfig();

        /**
         * If there's only one shipping method available we might want to preselect
         * it based on how the module is configured in admin
         **/
        if ($config->preselectSingleShippingMethod()
                || $config->preselectCheapestShippingMethod()
                || $config->hideIfFreeShipping()) {

            /* @var $quote Mage_Sales_Model_Quote */
            $quote  = $this->_getQuote();
            $groups = $quote->getShippingAddress()->getGroupedAllShippingRates();

            if (!empty($groups)) {
                foreach ($groups as $code => $groupItems) {
                    /**
                     * Preselect cheapest shipping method
                     **/
                    if ($config->preselectCheapestShippingMethod()) {
                        foreach ($groupItems as $item) {
                            if (!isset($shippingMethod)
                                || $shippingMethod->getPrice() > $item->getPrice()) {

                                $shippingMethod = $item;
                            }
                        }
                    } else {
                        if ($code == self::LESSFRICTION_FREESHIPPING) {
                            if (count($groupItems) == 1) {
                                $shippingMethod = $groupItems[0];
                            }
                        } else if (count($groups) == 1 && count($groupItems) == 1) {
                            $shippingMethod = $groupItems[0];
                        }
                    }
                }

                $quote->getShippingAddress()->setShippingMethod($shippingMethod->getCode());
            }
        }
    }

    /**
     * Get quote
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Get config model
     *
     * @return KL_LessFriction_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getModel('lessfriction/config');
    }
}
