<?php
/**
 * Less Friction
 * Copyright (C) 2013 Karlsson & Lord AB
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Payment helper
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Helper
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
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
     * @param object $method Payment method
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

        if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
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
                || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))
            ) {
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
