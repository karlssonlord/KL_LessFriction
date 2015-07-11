<?php 

class KL_LessFriction_Model_Validator_CheapestShippingMethodShouldBeSelected implements KL_LessFriction_Model_Validator
{

    public function validate()
    {
        return (bool)Mage::getModel('lessfriction/config')->preselectCheapestShippingMethod();
    }
}