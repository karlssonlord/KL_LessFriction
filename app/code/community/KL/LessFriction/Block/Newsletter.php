<?php
/**
 * Payment block
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage KL_LessFriction_Block
 * @author     Erik Eng <erik@karlssonlord.com>
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Block_Newsletter
    extends Mage_Checkout_Block_Onepage_Abstract
{
    /**
     * Is checked
     *
     * @return bool
     */
    public function isChecked()
    {
        $isChecked = (bool) $this->getCheckout()->getCustomerIsSubscribed();

        return $isChecked;
    }
}
