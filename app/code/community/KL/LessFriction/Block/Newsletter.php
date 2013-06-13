<?php
class KL_LessFriction_Block_Newsletter extends Mage_Checkout_Block_Onepage_Abstract
{
    public function __construct()
    {
        parent::__construct();
        // TODO: Set template in layout xml
        $this->setTemplate('kl/checkout/newsletter.phtml');
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