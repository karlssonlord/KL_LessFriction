<?php
class KL_LessFriction_Block_Shipping_Method
    extends Mage_Checkout_Block_Onepage_Shipping_Method
{
    /**
     * Is shipping method block visible?
     *
     * @return bool
     */
    public function isVisible()
    {
        $_shippingAddress    = $this->getQuote()->getShippingAddress();
        $_shippingRateGroups = $_shippingAddress->getGroupedAllShippingRates();

        if (
            count($_shippingRateGroups) == 1 &&
            count(current($_shippingRateGroups)) == 1 &&
            $_shippingAddress->getShippingMethod() &&
            Mage::getModel('lessfriction/config')->preselectSingleShippingMethod()
        ) {
            return false;
        }

        if (
            Mage::getModel('lessfriction/config')->hideIfFreeShipping() &&
            $_shippingAddress->getShippingMethod() == 'freeshipping_freeshipping'
        ) {
            return false;
        }

        return true;
    }
}
