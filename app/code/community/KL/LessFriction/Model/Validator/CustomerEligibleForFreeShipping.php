<?php

/**
 * @author David Wickström <david@karlssonlord.com>
 */
class KL_LessFriction_Model_Validator_CustomerEligibleForFreeShipping implements KL_LessFriction_Model_Validator
{
    /**
     * @return bool
     */
    public function validate()
    {
        if ($this->customerHasHighEnoughSubtotal() && $this->freeShippingMethodIsEnabled()) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    protected function customerHasHighEnoughSubtotal()
    {
        return Mage::helper('lessfriction/shipping')->customerIsEligibleForFreeShipping();
    }

    /**
     * @return bool
     */
    protected function freeShippingMethodIsEnabled()
    {
        return Mage::helper('lessfriction/shipping')->freeShippingMethodIsEnabled();
    }

}
