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
 * Data helper
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Helper
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get module store config by passing group and field
     *
     * @param string  $group Configuration group
     * @param string  $field Configuration field
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

    /**
     * Get sections
     *
     * @param array $steps Checkout steps
     *
     * @return void
     */
    public function getSections(array $steps = array())
    {
        $containerCodes = Mage::getSingleton('lessfriction/source_sections')
            ->toOptionArray();

        $sections = array();

        foreach (array_keys($containerCodes) as $code) {
            $sections[$code] = array();
        }

        foreach ($steps as $stepCode => $stepInfo) {
            $xmlPath = Mage::getStoreConfig(
                sprintf('lessfriction/layout/%s', $stepCode)
            );
            if ((Mage::getModel('checkout/session')->getQuote()->hasItems()
                && $stepCode != 'cart') || $stepCode == 'cart') {
            switch ($stepCode) {
                case 'login':
                    if ($this->hideLogin() === true) {

                    } else {
                        $sections[$xmlPath][$stepCode] = $stepInfo;
                    }
                    break;
                case 'crosssell':
                    if (Mage::getModel('lessfriction/config')->showCrosssell()) {
                        $sections[$xmlPath][$stepCode] = $stepInfo;
                    }
                    break;
                case 'cart':
                    if ($this->includeCart()) {
                        $sections[$xmlPath][$stepCode] = $stepInfo;
                    } else {

                    }
                    break;
                case 'shipping':
                    if (!Mage::getModel('checkout/session')->getQuote()->isVirtual()) {
                        $sections[$xmlPath][$stepCode] = $stepInfo;
                    }
                    break;
                default:
                    $sections[$xmlPath][$stepCode] = $stepInfo;
                    break;
            }
            }
        }

        return $sections;
    }

    /**
     * Newsletter helper methods
     *
     * @return boolean
     */
    public function isNewsletterEnabled()
    {
        return Mage::getStoreConfigFlag('newsletter/lessfriction/enable');
    }

    /**
     * Is newsletter checked?
     *
     * @return boolean
     */
    public function isNewsletterChecked()
    {
        return Mage::getStoreConfigFlag('newsletter/lessfriction/checked');
    }

    /**
     * Is the newsletter option visible for guests?
     *
     * @return boolean
     */
    public function isNewsletterVisibleGuest()
    {
        return Mage::getStoreConfigFlag('newsletter/lessfriction/visible_guest');
    }

    /**
     * Is the newsletter option visible for registered users?
     *
     * @return boolean
     */
    public function isNewsletterVisibleRegister()
    {
        return Mage::getStoreConfigFlag('newsletter/lessfriction/visible_register');
    }
}
