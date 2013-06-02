<?php
class KL_LessFriction_Model_Config
{
    
    /**
     * Get module store config by passing group and field
     *
     * @param string $group
     * @param string $field
     *
     * @return mixed
     */
    protected function _getStoreConfig($group, $field)
    {
        $setting = Mage::getStoreConfig(sprintf('lessfriction/%s/%s', $group, $field));
        return $setting;
    }

    public function isEnabled()
    {
        
    }

    public function showCrosssell()
    {
        $type = Mage::getStoreConfigFlag('lessfriction/crosssell/type');
        return $type == 'none';
    }

    public function preselectSinglePaymentMethod()
    {
        return Mage::getStoreConfigFlag('lessfriction/payment/preselect_single');
    }

    public function hideIfFreeShipping()
    {
        return Mage::getStoreConfigFlag('lessfriction/shipping/hide_if_freeshipping');
    }

    public function preselectSingleShippingMethod()
    {
        return Mage::getStoreConfigFlag('lessfriction/shipping/preselect_single');
    }

    public function preselectCheapestShippingMethod()
    {
        return Mage::getStoreConfigFlag('lessfriction/shipping/preselect_cheapest');
    }
}
