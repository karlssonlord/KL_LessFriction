<?php
/**
 * Less Friction
 * Copyright (C) 2013 Karlsson & Lord AB
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @author     Erik Eng <erik@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    LGPL v2.1 http://choosealicense.com/licenses/lgpl-v2.1/
 */

/**
 * Observer model
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @author     Erik Eng <erik@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    LGPL v2.1 http://choosealicense.com/licenses/lgpl-v2.1/
 */
class KL_LessFriction_Model_Observer
{
    /**
     * Shipping method code for free shipping
     *
     * @var string
     */
    const LESSFRICTION_FREESHIPPING = 'freeshipping';

    /**
     * Default country
     *
     * @var mixed
     */
    public $defaultCountry = null;

    /**
     * Pre dispatch
     *
     * @param Varien_Event_Observer $observer Observer object
     *
     * @return void
     */
    public function preDispatch(Varien_Event_Observer $observer)
    {
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $this->_getQuote();

        /* @var $address Mage_Sales_Model_Quote_Address */
        $billingAddress  = $quote->getBillingAddress();

        /* @var $address Mage_Sales_Model_Quote_Address */
        $shippingAddress = $quote->getShippingAddress();

        /* @var $store Mage_Core_Model_Store */
        $store           = Mage::app()->getStore();

        /* @var $allowedCountriesConfig string */
        $allowedCountriesConfig = Mage::getStoreConfig(
            'general/country/allow', $store->getId()
        );

        /* @var $allowedCountries array */
        $allowedCountries = explode(',', $allowedCountriesConfig);

        if ($billingAddress) {
            if (!$billingAddress->getCountryId()
                || !in_array($billingAddress->getCountryId(), $allowedCountries)
            ) {
                $billingAddress->setCountryId($this->_getDefaultCountry()->getCountryId())->save();
            }
        }

        if ($shippingAddress) {
            if (!$shippingAddress->getCountryId()
                || !in_array($shippingAddress->getCountryId(), $allowedCountries)
                || $shippingAddress->getShippingRatesCollection()->count() == 1
            ) {
                $shippingAddress
                    ->setCountryId($this->_getDefaultCountry()->getCountryId())
                    ->setCollectShippingRates(true)
                    ->collectShippingRates()
                    ->save();
            } elseif ($shippingAddress->getCountryId()) {
                $shippingAddress
                    ->setCollectShippingRates(true)
                    ->collectShippingRates()
                    ->save();
            }
        }

        if ($billingAddress && $shippingAddress) {
            $this->_setPaymentMethod();
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
        if ($this->defaultCountry === null) {
            $countryId = Mage::registry('client_country_id') ?
                            Mage::registry('client_country_id') :
                            Mage::helper('core')->getDefaultCountry();

            $this->defaultCountry = new Varien_Object(array('country_id' => $countryId));
        }

        return $this->defaultCountry;
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
                    || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))
                ) {
                    $availableMethods[] = $method->getCode();
                } else {
                    unset($methods[$key]);
                }
            }

            if (count($availableMethods) == 1) {
                Mage::getModel('checkout/type_onepage')
                    ->savePayment(array('method' => current($availableMethods)));
            }
        }
    }

    /**
     * Check if customer can use payment method
     *
     * @param object $method Payment method
     *
     * @return boolean
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

        if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
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
            || $config->hideIfFreeShipping()
        ) {

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
                                || $shippingMethod->getPrice() > $item->getPrice()
                            ) {

                                $shippingMethod = $item;
                            }
                        }
                    } else {
                        if ($code == self::LESSFRICTION_FREESHIPPING) {
                            if (count($groupItems) == 1) {
                                $shippingMethod = $groupItems[0];
                            }
                        } elseif (count($groups) == 1 && count($groupItems) == 1) {
                            $shippingMethod = $groupItems[0];
                        }
                    }
                }

                $quote->getShippingAddress()->setShippingMethod(
                    $shippingMethod->getCode()
                );
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

    /**
     * Set customer is subscribed
     *
     * @param object $observer Observer object
     *
     * @return void
     */
    public function setCustomerIsSubscribed($observer)
    {
        if (Mage::getSingleton('checkout/session')->getCustomerIsSubscribed()) {
            $quote    = $observer->getEvent()->getQuote();
            $customer = $quote->getCustomer();

            switch ($quote->getCheckoutMethod()) {
                case Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER:
                    $customer->setIsSubscribed(1);
                    break;
                case Mage_Sales_Model_Quote::CHECKOUT_METHOD_LOGIN_IN:
                    $customer->setIsSubscribed(1);
                    break;
                case Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST:
                    $session = Mage::getSingleton('core/session');

                    try {
                        $status = Mage::getModel('newsletter/subscriber')
                            ->subscribe($quote->getBillingAddress()->getEmail());

                        if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                            $session->addSuccess(
                                Mage::helper('lessfriction')->__(
                                    'Confirmation request has been sent '
                                    . 'regarding your newsletter subscription'
                                )
                            );
                        }
                    } catch (Mage_Core_Exception $e) {
                        $session->addException(
                            $e,
                            Mage::helper('lessfriction')->__(
                                'There was a problem with the newsletter '
                                . 'subscription: %s', $e->getMessage()
                            )
                        );
                    } catch (Exception $e) {
                        $session->addException(
                            $e,
                            Mage::helper('lessfriction')->__(
                                'There was a problem with the newsletter '
                                . 'subscription'
                            )
                        );
                    }

                    break;
            }

            Mage::getSingleton('checkout/session')->setCustomerIsSubscribed(0);
        }
    }

    /**
     * Add guset order to customer object
     *
     * @param Varien_Event_Observer $observer Observer object
     *
     * @return void
     */
    public function addGuestOrderToCustomer($observer)
    {
        /**
         * It doesn't hurt to be extra careful when fiddling with the
         * orders, customer might want to be redirected to a third
         * party for payment – then we don't want to risk any
         * unexpected exceptions.
         **/
        try {
            Mage::log('trying to merge', null, 'merge.log', true);
            $order = $observer->getOrder();

            if ($order && !$order->getCustomerId()) {
                Mage::log('we have the order', null, 'merge.log', true);
                $customer = Mage::getModel("customer/customer")
                    ->setWebsiteId(Mage::app()->getWebsite()->getId())
                    ->loadByEmail($order->getCustomerEmail());

                $order->setCustomer($customer);
                $order->setCustomerIsGuest(0);
            }
        } catch (Exception $e) {
            Mage::log('failed to merge', null, 'merge.log', true);
        }
    }
}
