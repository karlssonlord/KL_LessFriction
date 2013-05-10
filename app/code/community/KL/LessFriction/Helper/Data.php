<?php
class KL_LessFriction_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get module store config by passing group and field
     *
     * @param string  $group
     * @param string  $field
     * @param boolean $flag  If true a boolean will be returned.
     *
     * @return mixed
     */
    protected function _getStoreConfig($group, $field, $flag = false)
    {
        $xmlPath = sprintf('lessfriction/%s/%s', $group, $field);

        if ($flag === true) {
            $setting = Mage::getStoreConfigFlag($xmlPath);
        } else {
            $setting = Mage::getStoreConfig($xmlPath);
        }
        
        return $setting;
    }

    /**
     * Is Less Friction checkout active
     *
     * @return boolean
     */
    public function isActive()
    {
        $setting = $this->_getStoreConfig('general', 'active', true);
        return $setting;
    }

    /**
     * Hide login
     *
     * @return boolean
     */
    public function hideLogin()
    {
        $setting = $this->_getStoreConfig('login', 'hide', true);
        return $setting;
    }

    /**
     * Should cart be included in checkout
     *
     * @return boolean
     */
    public function includeCart()
    {
        $setting = $this->_getStoreConfig('cart', 'include_cart', true);
        return $setting;
    }

    public function getSections(array $steps = array(), Mage_Core_Block_Abstract $block = null)
    {
        $containerCodes = Mage::getSingleton('lessfriction/source_sections')->toOptionArray();

        $sections = array();

        foreach (array_keys($containerCodes) as $code) {
            $sections[$code] = array();
        }

        foreach ($steps as $stepCode => $stepInfo) {
            $xmlPath = Mage::getStoreConfig(sprintf('lessfriction/layout/%s', $stepCode));

            switch ($stepCode) {
                case 'login':
                    if ($this->hideLogin() === true) {

                    } else {
                        $sections[$xmlPath][$stepCode] = $stepInfo;
                    }
                    break;
                case 'cart':
                    if ($this->includeCart()) {
                        $sections[$xmlPath][$stepCode] = $stepInfo;
                    } else {
                        
                    }
                    break;
                case 'payment':
                    $sections[$xmlPath][$stepCode] = $stepInfo;
                    break;
                default:
                    $sections[$xmlPath][$stepCode] = $stepInfo;
                    break;
            }
        }

        return $sections;
    }
}
