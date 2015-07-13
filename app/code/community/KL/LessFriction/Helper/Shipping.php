<?php

/**
 * @author David Wickström <david@karlssonlord.com>
 */
class KL_LessFriction_Helper_Shipping extends Mage_Core_Helper_Abstract
{
    /**
     * There are a bunch of different shipping methods that has a "free shipping" option.
     * List them here to cover more cases
     *
     * @var array
     */
    protected $freeShippingMethods = array(
        'carriers/freeshipping/active',
        // 'matrixrates/something/something'...
    );

    /**
     * @return bool
     */
    public function customerIsEligibleForFreeShipping()
    {
        return $this->calculateAmountLeftUntilFreeShippingActivates() == 0;
    }

    /**
     * @return mixed
     */
    public function calculateAmountLeftUntilFreeShippingActivates()
    {
        $calculatedDifference = Mage::getStoreConfig('carriers/freeshipping/free_shipping_subtotal') - $this->getSubtotal();
        return max($calculatedDifference, 0); // Don't return negative numbers
    }

    /**
     * @return mixed
     */
    public function getSubtotal()
    {
        $totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();

        return $totals['subtotal']->getValueInclTax();
    }

    public function freeShippingMethodIsEnabled()
    {
        $active = array();
        foreach ($this->freeShippingMethods as $method) {
            $active[] = Mage::getStoreConfig($method);
        }
        return in_array("1", $active);
    }

}