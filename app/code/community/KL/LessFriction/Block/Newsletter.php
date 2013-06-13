<?php
class KL_LessFriction_Block_Newsletter extends Mage_Checkout_Block_Onepage_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Is checked
     *
     * @return bool
     */
    public function isChecked()
    {
        return (bool) $this->getCheckout()->getCustomerIsSubscribed();
    }
}