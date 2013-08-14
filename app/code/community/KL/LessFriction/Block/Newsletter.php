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
 * @subpackage KL_LessFriction_Block
 * @author     Erik Eng <erik@karlssonlord.com>
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    LGPL v2.1 http://choosealicense.com/licenses/lgpl-v2.1/
 */

/**
 * Newsletter block
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage KL_LessFriction_Block
 * @author     Erik Eng <erik@karlssonlord.com>
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    LGPL v2.1 http://choosealicense.com/licenses/lgpl-v2.1/
 */
class KL_LessFriction_Block_Newsletter
    extends Mage_Checkout_Block_Onepage_Abstract
{
    /**
     * Is checked
     *
     * @return bool
     */
    public function isChecked()
    {
        $isChecked = (bool) $this->getCheckout()->getCustomerIsSubscribed();

        return $isChecked;
    }
}
