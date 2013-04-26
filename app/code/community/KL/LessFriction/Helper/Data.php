<?php
class KL_LessFriction_Helper_Data extends Mage_Core_Helper_Abstract
{

	protected function _getStoreConfig($group, $field)
	{
		$setting = Mage::getStoreConfig(sprintf('lessfriction/%s/%s', $group, $field));
		return $setting;
	}

	public function isActive()
	{
		$setting = $this->_getStoreConfig('general', 'active');
		return $setting;
	}

	public function includeCart()
	{
		$setting = $this->_getStoreConfig('cart', 'include_cart');
		return $setting;
	}
}
