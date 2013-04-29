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
}
