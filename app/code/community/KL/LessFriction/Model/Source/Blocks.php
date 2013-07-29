<?php
/**
 * Source model for static blocks
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Model_Source_Blocks
{
    /**
     * Options array
     *
     * @var mixed
     */
    protected $_options;

    /**
     * Get available static blocks in an option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => 0,
                    'label' => Mage::helper('catalog')
                        ->__('Please select static block...'),
                )
            );

            $options = Mage::getResourceModel('cms/block_collection')
                ->toOptionArray();
            $this->_options = array_merge($this->_options, $options);
        }

        return $this->_options;
    }
}
