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
 * @author     Erik Eng <erik@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    LGPL v2.1 http://choosealicense.com/licenses/lgpl-v2.1/
 */

/**
 * Onepage checkout model
 *
 * Don't use this class. Try to move its code to
 * KL_LessFriction_Model_Type_LessFriction instead.
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Erik Eng <erik@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    LGPL v2.1 http://choosealicense.com/licenses/lgpl-v2.1/
 */
class KL_LessFriction_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    /**
     * Save billing
     *
     * @param array $data              Address data
     * @param int   $customerAddressId Customer address id
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function saveBilling($data, $customerAddressId)
    {
        if (isset($data['is_subscribed']) && !empty($data['is_subscribed'])) {
            $this->getCheckout()->setCustomerIsSubscribed(1);
        } else {
            $this->getCheckout()->setCustomerIsSubscribed(0);
        }

        return parent::saveBilling($data, $customerAddressId);
    }
}
