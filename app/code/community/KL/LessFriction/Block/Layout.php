<?php
class KL_LessFriction_Block_Layout extends Mage_Checkout_Block_Onepage
{
    /**
     * Get 'one step checkout' step data
     *
     * @return array
     */
    public function getSteps()
    {
        $steps       = array();
        $stepCodes   = $this->_getStepCodes();
        $stepCodes[] = 'crosssell';
        $stepCodes[] = 'cart';
        $checkout    = $this->getCheckout();

        if ($this->isCustomerLoggedIn()) {
            $stepCodes = array_diff($stepCodes, array('login'));
        }

        foreach ($stepCodes as $step) {
            $steps[$step] = $checkout->getStepData($step);
        }

        return $steps;
    }
}