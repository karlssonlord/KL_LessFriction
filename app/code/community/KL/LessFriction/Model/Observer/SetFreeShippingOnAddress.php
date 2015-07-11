<?php 

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
        $quote = $observer->getEvent()->getAddress()->getQuote();
        if ($this->validatorsPass()) {
            $this->setFreeShippingOnQuote($quote);
        } else {
            // TODO: Works, but need to un-hard-code and grab a shipping method from a MethodProvider class
            $quote->getShippingAddress()->setShippingMethod('flatrate_flatrate');
        }

        return $this;
    }

    /**
     * Check if shipping method contains the word "free". Should be enough.
     *
     * @param $quote
     * @return bool
     */
    protected function hasFreeShippingSetAlready($quote)
    {
        return strpos($quote->getShippingAddress()->getShippingMethod(), 'free') !== false;
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

    /**
     * @param $quote
     * @return bool
     */
    protected function shippingRateNeedsToBeCollected($quote)
    {
        return $this->hasFreeShippingSetAlready($quote) || $this->hasNoShippingMethodSet($quote);
    }

    protected function hasNoShippingMethodSet($quote)
    {
        return $quote->getShippingAddress()->getShippingMethod() === null;
    }

}