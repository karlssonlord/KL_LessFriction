<?php
class KL_LessFriction_Block_Address_Billing
    extends Mage_Checkout_Block_Onepage_Billing
{
    public function getAddress()
    {
        if (is_null($this->_address)) {
            $this->_address = $this->getQuote()->getBillingAddress();

            if ($this->isCustomerLoggedIn()) {
                if(!$this->_address->getFirstname()) {
                    $this->_address->setFirstname($this->getQuote()->getCustomer()->getFirstname());
                }
                if(!$this->_address->getLastname()) {
                    $this->_address->setLastname($this->getQuote()->getCustomer()->getLastname());
                }
            }
        }

        return $this->_address;
    }
}