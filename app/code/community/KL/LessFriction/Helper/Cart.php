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
 * Cart helper
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Helper
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Helper_Cart extends Mage_Checkout_Helper_Cart
{
    /**
     * Retrieve url for remove product from cart
     *
     * @param Mage_Sales_Quote_Item $item Quote item
     *
     * @return string
     */
    public function getRemoveJsonUrl($item)
    {
        $params = array(
            'id' => $item->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_BASE64_URL => $this->getCurrentBase64Url()
        );

        return $this->_getUrl('checkout/cart/deleteJson', $params);
    }

    /**
     * Retrieve url to subtract qty of product in cart
     *
     * @param Mage_Sales_Quote_Item $item Quote item
     *
     * @return string
     */
    public function getSubtractUrl($item)
    {
        $continueUrl    = Mage::helper('core')->urlEncode($this->getCurrentUrl());
        $urlParamName   = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $qty            = (int) $item->getQty() - 1;

        if ($qty <= 0) {
            return $this->getRemoveJsonUrl($item);
        }

        $routeParams = array(
            $urlParamName => $continueUrl,
            'id'          => $item->getId(),
            'qty'         => $qty
        );

        if ($this->_getRequest()->getRouteName() == 'checkout'
            && $this->_getRequest()->getControllerName() == 'cart'
        ) {
            $routeParams['in_cart'] = 1;
        }

        return $this->_getUrl('checkout/cart/updateItemOptionsJson', $routeParams);
    }

    /**
     * Retrieve url to subtract qty of product in cart
     *
     * @param Mage_Sales_Quote_Item $item Quote item
     * @param int                   $qty  Item quantity
     *
     * @return string
     */
    public function increaseQtyUrl($item, $qty = 1)
    {
        $continueUrl    = Mage::helper('core')->urlEncode($this->getCurrentUrl());
        $urlParamName   = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $qty            = (int) $item->getQty() + $qty;

        $routeParams = array(
            $urlParamName => $continueUrl,
            'id'          => $item->getId(),
            'qty'         => $qty
        );

        if ($this->_getRequest()->getRouteName() == 'checkout'
            && $this->_getRequest()->getControllerName() == 'cart'
        ) {
            $routeParams['in_cart'] = 1;
        }

        return $this->_getUrl('checkout/cart/updateItemOptionsJson', $routeParams);
    }
}
