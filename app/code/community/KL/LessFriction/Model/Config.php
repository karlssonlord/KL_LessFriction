<?php
/**
 * Less Friction
 * Copyright (C) 2013 Karlsson & Lord AB
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    LGPL v2.1 http://choosealicense.com/licenses/lgpl-v2.1/
 */

/**
 * Config model
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    LGPL v2.1 http://choosealicense.com/licenses/lgpl-v2.1/
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
        $type = Mage::getStoreConfig('lessfriction/cross_sell/type');
        return $type !== 'none';
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

    /**
     * Should guest orders be added to existing customers?
     *
     * @return boolean
     */
    public function addGuestOrdersToCustomer()
    {
        return Mage::getStoreConfigFlag('lessfriction/login/add_guest_orders_to_customer');
    }

    /**
     * Should checkout method be set to guest if customer email exists?
     *
     * @return boolean
     */
    public function switchToGuestCheckoutIfCustomerEmailExists()
    {
        return Mage::getStoreConfigFlag('lessfriction/login/customer_email_exists');
    }
}
