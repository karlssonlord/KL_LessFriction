<?php
/**
 * Shipping method block
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage KL_LessFriction_Block
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Block_Shipping_Method
    extends Mage_Checkout_Block_Onepage_Shipping_Method
{
    /**
     * Is shipping method block visible?
     *
     * @return boolean
     */
    public function isVisible()
    {
        $config              = Mage::getModel('lessfriction/config');
        $_shippingAddress    = $this->getQuote()->getShippingAddress();
        $_shippingRateGroups = $_shippingAddress->getGroupedAllShippingRates();
        $_shippingMethod     = $_shippingAddress->getShippingMethod();

        if (count($_shippingRateGroups) == 1
            && count(current($_shippingRateGroups)) == 1
            && $_shippingMethod
            && $config->preselectSingleShippingMethod()
        ) {
            return false;
        }

        if ($config->hideIfFreeShipping()
            && $_shippingMethod == 'freeshipping_freeshipping'
        ) {
            return false;
        }

        return true;
    }
}
