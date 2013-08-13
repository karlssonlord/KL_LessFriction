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
 * Source model for address types
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Model_Source_Addresses
{
    /**
     * Event name for hooking in to the method toOptionArray,
     * could be used for adding extra options to the array
     *
     * @var string
     */
    const EVENT_NAME = 'lessfriction_addresses';

    /**
     * Get available address types as an option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('lessfriction');

        $addresses = new Varien_Object(
            array(
                'billing'    => $helper->__('Billing'),
                'shipping'   => $helper->__('Shipping'),
            )
        );

        Mage::dispatchEvent(
            self::EVENT_NAME,
            array('addresses' => $addresses)
        );

        return (array) $addresses->getData();
    }
}
