<?php
/**
 * LessFriction checkout model
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Model_Type_LessFriction
    extends Mage_Checkout_Model_Type_Onepage
{
    /**
     * Save checkout shipping address
     *
     * @param array $data              Address data
     * @param int   $customerAddressId Customer address id
     *
     * @return Mage_Checkout_Model_Type_Onepage
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
