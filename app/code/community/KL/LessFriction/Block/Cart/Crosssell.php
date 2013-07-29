<?php
/**
 * Cart cross sell block
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage KL_LessFriction_Block
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Block_Cart_Crosssell
    extends Mage_Checkout_Block_Onepage_Abstract
{
    /**
     * Constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->getCheckout()->setStepData(
            'crosssell', array(
                'label'     => Mage::helper('checkout')->__('Cross Sell'),
                'is_show'   => true
            )
        );

        parent::_construct();
    }

    /**
     * Get static block
     *
     * @return mixed
     */
    public function getStaticBlock()
    {
        $blockId = Mage::getStoreConfig('lessfriction/cross_sell/block');

        if ($blockId) {
            return Mage::getModel('cms/block')->load($blockId)->getContent();
        } else {
            return false;
        }
    }
}
