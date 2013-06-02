<?php
class KL_LessFriction_Model_Type_LessFriction
    extends Mage_Checkout_Model_Type_Onepage
{
    /**
     * Save checkout shipping address
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  Mage_Checkout_Model_Type_Onepage
     */
    public function saveShipping($data, $customerAddressId)
    {
        parent::saveShipping($data, $customerAddressId);

        $address = $this->getQuote()->getShippingAddress();

        if (isset($data['use_for_billing']) && $data['use_for_billing'] == 1) {
            $result = $this->saveBilling($data, $customerAddressId);
            $address->setSameAsBilling(1)->save();

            return $result;
        }
    }
}