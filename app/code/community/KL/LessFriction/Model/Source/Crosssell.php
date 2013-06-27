<?php
class KL_LessFriction_Model_Source_Crosssell
{
    public function toOptionArray()
    {
        $helper = Mage::helper('lessfriction');

        $sections = new Varien_Object(array(
            'none' => $helper->__('None'),
            'crosssell' => $helper->__('Ordinary cross sell'),
            'combine'   => $helper->__('Combine CMS block and ordinary cross sell'),
            'cmsblock'  => $helper->__('Only CMS block'),
        ));

        return (array) $sections->getData();
    }
}
