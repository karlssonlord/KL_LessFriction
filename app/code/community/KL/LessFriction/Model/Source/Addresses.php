<?php
/**
 * Source model for address types
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Model_Source_Addresses
{
    /**
     * Event name for hooking in to the method toOptionArray,
     * could be used for adding extra options to the array
     *
     * @var string
     */
    const EVENT_NAME = 'lessfriction_addresses';

    /**
     * Get available address types as an option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('lessfriction');

        $addresses = new Varien_Object(
            array(
                'billing'    => $helper->__('Billing'),
                'shipping'   => $helper->__('Shipping'),
            )
        );

        Mage::dispatchEvent(
            self::EVENT_NAME,
            array('addresses' => $addresses)
        );

        return (array) $addresses->getData();
    }
}
