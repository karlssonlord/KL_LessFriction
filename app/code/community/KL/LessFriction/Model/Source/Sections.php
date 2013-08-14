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
 * Source model for checkout sections
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Model
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    LGPL v2.1 http://choosealicense.com/licenses/lgpl-v2.1/
 */
class KL_LessFriction_Model_Source_Sections
{
    /**
     * Event name for hooking in to the method toOptionArray,
     * could be used for adding extra options to the array
     *
     * @var string
     */
    const EVENT_NAME = 'lessfriction_sections';

    /**
     * Get available checkout step layout containers
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('lessfriction');

        // https://en.wikipedia.org/wiki/NATO_phonetic_alphabet
        $sections = new Varien_Object(
            array(
                'alfa'    => $helper->__('Alfa'),
                'bravo'   => $helper->__('Bravo'),
                'charlie' => $helper->__('Charlie'),
                'delta'   => $helper->__('Delta'),
                'echo'    => $helper->__('Echo'),
                'foxtrot' => $helper->__('Foxtrot'),
                'golf'    => $helper->__('Golf'),
                'hotel'   => $helper->__('Hotel'),
            )
        );

        Mage::dispatchEvent(
            self::EVENT_NAME,
            array('sections' => $sections)
        );

        return (array) $sections->getData();
    }
}
