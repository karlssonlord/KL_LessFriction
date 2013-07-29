<?php
/**
 * Source model for cross selling type
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Model_Source_Crosssell
{
    /**
     * Get available cross sell methods in an option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('lessfriction');

        $sections = new Varien_Object(
            array(
                'none'      => $helper->__('None'),
                'crosssell' => $helper->__('Ordinary cross sell'),
                'combine'   => $helper->__(
                    'Combine CMS block and ordinary cross sell'
                ),
                'cmsblock'  => $helper->__('Only CMS block'),
            )
        );

        return (array) $sections->getData();
    }
}
