<?php
class KL_LessFriction_Block_Payment
    extends Mage_Checkout_Block_Onepage_Payment
{
	public function isVisible()
	{
		if (Mage::getModel('lessfriction/config')->preselectSinglePaymentMethod()) {
			return false;
		}

		return true;
	}
}
