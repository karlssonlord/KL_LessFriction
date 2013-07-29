<?php
/**
 * Payment methods block
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage KL_LessFriction_Block
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Block_Payment_Methods
    extends Mage_Checkout_Block_Onepage_Payment_Methods
{
    /**
     * Is the payment methods visible?
     *
     * @return boolean
     */
    public function isVisible()
    {
        return true;
    }
}
