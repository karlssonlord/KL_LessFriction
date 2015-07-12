<?php

/**
 * @author David Wickström <david@karlssonlord.com>
 */
class KL_LessFriction_Model_Quote_Freeshipping extends Mage_SalesRule_Model_Quote_Freeshipping
{
    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        Mage::dispatchEvent('free_shipping_validation_point_was_passed', array('quote' => $address->getQuote()));
        return $this;
    }
}