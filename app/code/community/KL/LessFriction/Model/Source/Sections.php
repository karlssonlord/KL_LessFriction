<?php
class KL_LessFriction_Model_Source_Sections
{
    const EVENT_NAME = 'lessfriction_sections';
    
    /**
     * Get available checkout step layout containers
     *
     * @return array 
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('lessfriction');

        // https://en.wikipedia.org/wiki/NATO_phonetic_alphabet
        $sections = new Varien_Object(array(
            'alfa'    => $helper->__('Alfa'),
            'bravo'   => $helper->__('Bravo'),
            'charlie' => $helper->__('Charlie'),
            'delta'   => $helper->__('Delta'),
            'echo'    => $helper->__('Echo'),
            'foxtrot' => $helper->__('Foxtrot'),
            'golf'    => $helper->__('Golf'),
            'hotel'   => $helper->__('Hotel'),
        ));

        Mage::dispatchEvent(
            self::EVENT_NAME,
            array('sections' => $sections)
        );

        return (array) $sections->getData();
    }
}
