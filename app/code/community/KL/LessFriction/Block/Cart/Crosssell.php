<?php
class KL_LessFriction_Block_Cart_Crosssell
    extends Mage_Checkout_Block_Onepage_Abstract
{
    public function _construct()
    {
        $this->getCheckout()->setStepData('crosssell', array(
            'label'     => Mage::helper('checkout')->__('Cross Sell'),
            'is_show'   => true
        ));

        parent::_construct();
    }

    /**
     * Get static block
     *
     * @return mixed
     */
    public function getStaticBlock()
    {
        $id = Mage::getStoreConfig('lessfriction/cross_sell/block');

        if ($id) {
            return Mage::getModel('cms/block')->load($id)->getContent();
        } else {
            return false;
        }
    }
}
