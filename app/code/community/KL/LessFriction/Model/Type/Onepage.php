<?php
/**
 * Onepage checkout model
 *
 * Don't use this class. Try to move its code to
 * KL_LessFriction_Model_Type_LessFriction instead.
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Erik Eng <erik@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    /**
     * Save billing
     *
     * @param array $data              Address data
     * @param int   $customerAddressId Customer address id
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function saveBilling($data, $customerAddressId)
    {
        if (isset($data['is_subscribed']) && !empty($data['is_subscribed'])) {
            $this->getCheckout()->setCustomerIsSubscribed(1);
        } else {
            $this->getCheckout()->setCustomerIsSubscribed(0);
        }

        return parent::saveBilling($data, $customerAddressId);
    }
}
