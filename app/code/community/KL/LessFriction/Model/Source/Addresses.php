<?php
class KL_LessFriction_Model_Source_Addresses
{
    const EVENT_NAME = 'lessfriction_addresses';
    
    /**
     * Get available checkout step layout containers
     *
     * @return array 
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('lessfriction');

        $addresses = new Varien_Object(array(
            'billing'    => $helper->__('Billing'),
            'shipping'   => $helper->__('Shipping'),
        ));

        Mage::dispatchEvent(
            self::EVENT_NAME,
            array('addresses' => $addresses)
        );

        return (array) $addresses->getData();
    }
}
