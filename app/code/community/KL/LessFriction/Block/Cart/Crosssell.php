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
 * Cart cross sell block
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage KL_LessFriction_Block
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Block_Cart_Crosssell
    extends Mage_Checkout_Block_Onepage_Abstract
{
    /**
     * Constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->getCheckout()->setStepData(
            'crosssell', array(
                'label'     => Mage::helper('checkout')->__('Cross Sell'),
                'is_show'   => true
            )
        );

        parent::_construct();
    }

    /**
     * Get static block
     *
     * @return mixed
     */
    public function getStaticBlock()
    {
        $blockId = Mage::getStoreConfig('lessfriction/cross_sell/block');

        if ($blockId) {
            return Mage::getModel('cms/block')->load($blockId)->getContent();
        } else {
            return false;
        }
    }
}
