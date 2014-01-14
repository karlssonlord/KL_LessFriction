<?php
/**
 * Less Friction
 * Copyright (C) 2013 Karlsson & Lord AB
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage KL_LessFriction_Block
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    LGPL v2.1 http://choosealicense.com/licenses/lgpl-v2.1/
 */

/**
 * Billing address block
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage KL_LessFriction_Block
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    LGPL v2.1 http://choosealicense.com/licenses/lgpl-v2.1/
 */
class KL_LessFriction_Block_Address_Billing
    extends Mage_Checkout_Block_Onepage_Billing
{
    /**
     * Get address
     *
     * @return void
     */
    public function getAddress()
    {
        if (is_null($this->_address)) {
            $this->_address = $this->getQuote()->getBillingAddress();

            if ($this->isCustomerLoggedIn()) {

                $customer = $this->getQuote()->getCustomer();

                if (!$this->_address->getFirstname()) {
                    $this->_address->setFirstname($customer->getFirstname());
                }

                if (!$this->_address->getLastname()) {
                    $this->_address->setLastname($customer->getLastname());
                }
            }
        }

        return $this->_address;
    }

    /**
     * Is the billing address configured to be used as the primary address
     *
     * @return boolean
     */
    public function isPrimaryAddress()
    {
        if ($this->getQuote()->isVirtual()) {
            return true;
        }

        $type = Mage::getModel('lessfriction/config')->getPrimaryAddressType();
        $flag = ($type == 'billing');

        return $flag;
    }

    /**
     * Get HTML select element with the customers addresses
     *
     * @param string $type Shipping or billing address
     *
     * @return string
     */
    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {

            $options = array();

            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }

            $addressId = $this->getAddress()->getCustomerAddressId();

            if (empty($addressId)) {
                $customer = $this->getCustomer();

                if ($type =='billing') {
                    $address = $customer->getPrimaryBillingAddress();
                } else {
                    $address = $customer->getPrimaryShippingAddress();
                }

                if ($address) {
                    $addressId = $address->getId();
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams(
                    'onchange="' . $type . 'Address.newAddress(!this.value)"'
                )
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }

        return '';
    }
}
