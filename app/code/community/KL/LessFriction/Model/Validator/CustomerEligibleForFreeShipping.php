<?php 

class KL_LessFriction_Model_Validator_CustomerEligibleForFreeShipping implements KL_LessFriction_Model_Validator
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
        $active = array();
        foreach ($this->freeShippingMethods as $method) {
            $active[] = Mage::getStoreConfig($method);
        }
        return in_array("1", $active);
    }

}
