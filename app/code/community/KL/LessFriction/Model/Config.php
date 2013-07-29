<?php
/**
 * Config model
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Model_Config
{
    /**
     * Get module store config by passing group and field
     *
     * @param string $group Configuration group
     * @param string $field Configuration field
     *
     * @return mixed
     */
    protected function _getStoreConfig($group, $field)
    {
        $setting = Mage::getStoreConfig(sprintf('lessfriction/%s/%s', $group, $field));

        return $setting;
    }

    /**
     * Is the module enabled in the admin configuration?
     *
     * @return boolean
     *
     * @todo Implement this method
     */
    public function isEnabled()
    {
    }

    /**
     * Should cross sell information be shown in the checkout?
     *
     * @return boolean
     */
    public function showCrosssell()
    {
        $type = Mage::getStoreConfigFlag('lessfriction/crosssell/type');

        return $type == 'none';
    }

    /**
     * Should single payment method be pre-selected?
     *
     * @return boolean
     */
    public function preselectSinglePaymentMethod()
    {
        return Mage::getStoreConfigFlag('lessfriction/payment/preselect_single');
    }

    /**
     * Hide other shipping methods if free shipping is available?
     *
     * @return boolean
     */
    public function hideIfFreeShipping()
    {
        return Mage::getStoreConfigFlag('lessfriction/shipping/hide_if_freeshipping');
    }

    /**
     * Should single shipping method be pre-selected?
     *
     * @return boolean
     */
    public function preselectSingleShippingMethod()
    {
        return Mage::getStoreConfigFlag('lessfriction/shipping/preselect_single');
    }

    /**
     * Should cheapest shipping method be pre-selected?
     *
     * @return boolean
     */
    public function preselectCheapestShippingMethod()
    {
        return Mage::getStoreConfigFlag('lessfriction/shipping/preselect_cheapest');
    }

    /**
     * Get primary address type
     *
     * @return string
     */
    public function getPrimaryAddressType()
    {
        $type = Mage::getStoreConfig('lessfriction/addresses/primary');

        if (!$type) {
            $type = 'billing';
        }

        return $type;
    }
}
