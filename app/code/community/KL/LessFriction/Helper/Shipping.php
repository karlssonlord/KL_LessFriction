<?php 

class KL_LessFriction_Helper_Shipping extends Mage_Core_Helper_Abstract
{

    public function customerIsEligibleForFreeShipping()
    {
        return $this->calculateAmountLeftUntilFreeShippingActivates() == 0;
    }

    public function calculateAmountLeftUntilFreeShippingActivates()
    {
        $calculatedDifference = Mage::getStoreConfig('carriers/freeshipping/free_shipping_subtotal') - $this->getSubtotal();
        return max($calculatedDifference, 0); // Don't return negative numbers
    }

    public function getSubtotal()
    {
        $totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
        return $totals['subtotal']->getValueInclTax();
    }

}