<?php
class KL_LessFriction_Helper_Payment extends Mage_Core_Helper_Abstract
{
    /**
     * Should payment section be visible in cart?
     *
     * @return boolean
     */
    public function isVisible()
    {
        /**
         * @todo Add check of configuration setting
         **/
        if (count($this->_getMethods()) == 1) {
            return false;   
        } else {
            return true;
        }
    }

    /**
     * Check if customer can use payment method
     *
     * @param object $method
     *
     * @return boolean
     *
     * @see Mage_Payment_Block_Form_Container
     */
    protected function _canUseMethod($method)
    {
        if (!$method->canUseForCountry($this->getQuote()->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency($this->getQuote()->getStore()->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total    = $this->getQuote()->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve availale payment methods
     *
     * @return array
     *
     * @see Mage_Payment_Block_Form_Container
     */
    protected function _getMethods()
    {
        $quote = $this->getQuote();
        $store = $quote ? $quote->getStoreId() : null;
        $methods = Mage::helper('payment')->getStoreMethods($store, $quote);
        $total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();
        foreach ($methods as $key => $method) {
            if ($this->_canUseMethod($method)
                && ($total != 0
                    || $method->getCode() == 'free'
                    || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))) {
                // Do nothing
            } else {
                unset($methods[$key]);
            }
        }

        return $methods;
    }

    /**
     * Get quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getModel('checkout/type_onepage')->getQuote();
    }
}
