<?php 

class KL_LessFriction_Model_Quote_Freeshipping extends Mage_SalesRule_Model_Quote_Freeshipping
{
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        Mage::dispatchEvent('free_shipping_validation_point_was_passed', array('address' => $address));
        return $this;
    }
}