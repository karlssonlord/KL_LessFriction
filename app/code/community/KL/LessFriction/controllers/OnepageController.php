<?php
/**
 * Require KL_Checkout_OnepageController that is being extended by
 * Mage_Checkout_OnepageController.
 */
require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'OnepageController.php';

/**
 * Onepage Checkout Controller
 *
 * @package default
 */
class KL_LessFriction_OnepageController extends Mage_Checkout_OnepageController
{
    const LAYOUT_HANDLE_BASE    = 'lessfriction_boilerplate';
    const LAYOUT_HANDLE_DEFAULT = 'lessfriction_default';

    /**
     * Index
     *
     * @return void
     */
    public function indexAction()
    {
        if ($this->_getHelper()->isActive() == false) {
            parent::indexAction();
            return;
        }

        /**
         * If checkout is disabled
         **/
        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }

        $quote       = $this->_getQuote();
        $includeCart = $this->_getHelper()->includeCart();

        /**
         * If quote is empty or has errors
         **/
        if (!$quote->hasItems() || $quote->getHasError()) {
            if ($includeCart == false) {
                $this->_redirect('checkout/cart');
                return;
            }
        }

        /**
         * If quote subtotal is below minimum amount
         **/
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                Mage::getStoreConfig('sales/minimum_order/error_message') :
                Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');
            Mage::getSingleton('checkout/session')->addError($error);

            if ($includeCart == false) {
                $this->_redirect('checkout/cart');
                return;
            }
        }

        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(
            Mage::getUrl(
                '*/*/*',
                array('_secure' => true)
            )
        );

        $this->getOnepage()->initCheckout();

        $this->loadLayout();

        /**
         * Inititalize session messages
         **/
        $this->_initLayoutMessages('customer/session');

        if ($includeCart == true) {
            $this->_initLayoutMessages('checkout/session');
        }

        $this->getLayout()->getBlock('head')->setTitle(
            $this->__('Less Friction Checkout')
        );

        $this->renderLayout();
    }

    /**
     * Save checkout method
     *s
     * @return void
     */
    public function saveMethodAction()
    {
        if ($this->_getHelper()->isActive() === false) {
            parent::saveMethodAction();
            return;
        }

        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            $method = $this->getRequest()->getPost('method');
            $result = $this->getOnepage()->saveCheckoutMethod($method);
            $this->_jsonResponse($result);
        }
    }

    /**
     * Save billing address
     *
     * @return void
     */
    public function saveBillingAction()
    {
        if ($this->_getHelper()->isActive() == false) {
            parent::saveBillingAction();
            return;
        }
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
        if ($this->_getHelper()->isActive() == false) {
            parent::saveShippingAction();
            return;
        }
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
        if ($this->_getHelper()->isActive() == false) {
            parent::saveShippingMethodAction();
            return;
        }
    }

    /**
     * Save payment method
     *
     * @return void
     */
    public function savePaymentAction()
    {
        if ($this->_getHelper()->isActive() == false) {
            parent::savePaymentAction();
            return;
        }
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
        if ($this->_getHelper()->isActive() == false) {
            parent::saveOrderAction();
            return;
        }
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

    /**
     * Get quote
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        $quote = $this->getOnepage()->getQuote();
        return $quote;
    }

    /**
     * Get helper
     *
     * @return KL_LessFriction_Helper_Data
     */
    protected function _getHelper()
    {
        $helper = Mage::helper('lessfriction');
        return $helper;
    }

    /**
     * Adds checkitout layout handles if it is enabled
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function addActionLayoutHandles()
    {
        parent::addActionLayoutHandles();

        if ($this->_getHelper()->isActive() == true) {
            $this->getLayout()->getUpdate()->addHandle(self::LAYOUT_HANDLE_BASE);
            $this->getLayout()->getUpdate()->addHandle(self::LAYOUT_HANDLE_DEFAULT);
        }

        return $this;
    }
}
