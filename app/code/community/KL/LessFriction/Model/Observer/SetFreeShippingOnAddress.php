<?php

/**
 * @author David Wickström <david@karlssonlord.com>
 */
class KL_LessFriction_Model_Observer_SetFreeShippingOnAddress
{
    /**
     * Validation rules for setting free shipping
     *
     * @var array
     */
    protected $validators = array(
        'KL_LessFriction_Model_Validator_CustomerEligibleForFreeShipping',
        'KL_LessFriction_Model_Validator_CheapestShippingMethodShouldBeSelected'
    );

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function handle(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        if ($this->validatorsPass()) {
            $this->setFreeShippingOnQuote($quote);
        } else {
            Mage::dispatchEvent('customer_was_not_eligible_for_free_shipping', array('quote' => $quote));
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function validatorsPass()
    {
        $result = array();
        foreach ($this->validators as $validatorClassPath) {
            $validator = $this->getValidator($validatorClassPath);
            $result[] = $validator->validate();
        }
        return !in_array(false, $result);
    }

    /**
     * @param $quote
     */
    protected function setFreeShippingOnQuote($quote)
    {
        $quote->getShippingAddress()->setShippingMethod('freeshipping_freeshipping');
        $quote->save();
    }

    /**
     * @param $validatorClassPath
     * @return mixed
     */
    protected function getValidator($validatorClassPath)
    {
        return new $validatorClassPath;
    }

}
