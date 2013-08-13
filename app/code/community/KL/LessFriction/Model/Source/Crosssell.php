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
 * Source model for cross selling type
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 */
class KL_LessFriction_Model_Source_Crosssell
{
    /**
     * Get available cross sell methods in an option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('lessfriction');

        $sections = new Varien_Object(
            array(
                'none'      => $helper->__('None'),
                'crosssell' => $helper->__('Ordinary cross sell'),
                'combine'   => $helper->__(
                    'Combine CMS block and ordinary cross sell'
                ),
                'cmsblock'  => $helper->__('Only CMS block'),
            )
        );

        return (array) $sections->getData();
    }
}
