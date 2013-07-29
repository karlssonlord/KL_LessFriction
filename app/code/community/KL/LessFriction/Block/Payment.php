<?php
/**
 * Payment block
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage KL_LessFriction_Block
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Block_Payment
    extends Mage_Checkout_Block_Onepage_Payment
{
    /**
     * Return boolean telling if the payment block should be visible or not
     *
     * @return boolean
     */
    public function isVisible()
    {
        $config = Mage::getModel('lessfriction/config');

        if ($config->preselectSinglePaymentMethod()) {
            return false;
        }

        return true;
    }
}
