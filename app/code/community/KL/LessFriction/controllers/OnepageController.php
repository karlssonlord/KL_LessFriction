<?php
/**
 * Require KL_Checkout_OnepageController that is being extended by
 * Mage_Checkout_OnepageController.
 */
require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'OnepageController.php';

class KL_LessFriction_OnepageController extends Mage_Checkout_OnepageController
{
    /**
     * Index
     *
     * @return void
     */
    public function indexAction()
    {
    }

    /**
     * Save checkout method
     */
    public function saveMethodAction()
    {
    }

    /**
     * Save billing address
     *
     * @return void
     */
    public function saveBillingAction()
    {
    }

    /**
     * Save billing address (alias)
     *
     * @return void
     */
    public function saveBillingAddressAction()
    {
        $this->saveBillingAction();
    }

    /**
     * Save shipping address
     *
     * @return void
     */
    public function saveShippingAction()
    {
    }

    /**
     * Save shipping address (alias)
     *
     * @return void
     */
    public function saveShippingAddressAction()
    {
        $this->saveShippingAction();
    }

    /**
     * Save shipping method
     *
     * @return void
     */
    public function saveShippingMethodAction()
    {
    }

    /**
     * Save payment method
     *
     * @return void
     */
    public function savePaymentAction()
    {
    }

    /**
     * Save payment method (alias)
     *
     * @return void
     */
    public function savePaymentMethodAction()
    {
        $this->savePaymentAction();
    }

    /**
     * Save order
     *
     * @return void
     */
    public function saveOrderAction()
    {
    }

    /**
     * Genereate JSON response
     *
     * @return void
     */
    protected function _jsonResponse($data = array())
    {
        $jsonData = Mage::helper('core')->jsonEncode($data);
        $this->getResponse()->setBody($jsonData);
    }
}
