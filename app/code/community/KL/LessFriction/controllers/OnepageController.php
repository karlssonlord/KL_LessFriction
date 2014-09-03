<?php
/**
 * Less Friction
 *
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
 * @subpackage Controllers
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    http://choosealicense.com/licenses/gpl-v2/ GPL v2
 */

require_once Mage::getModuleDir('controllers', 'Mage_Checkout')
    . DS . 'OnepageController.php';

/**
 * Onepage Checkout Controller
 *
 * @category   KL
 * @package    KL_LessFriction
 * @subpackage Controllers
 * @author     Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright  2013 Karlsson & Lord AB
 * @license    http://choosealicense.com/licenses/gpl-v2/ GPL v2
 */
class KL_LessFriction_OnepageController extends Mage_Checkout_OnepageController
{
    const LAYOUT_HANDLE_BASE    = 'lessfriction_boilerplate';
    const LAYOUT_HANDLE_DEFAULT = 'lessfriction_default';
    const LAYOUT_HANDLE_JSON    = 'lessfriction_json';
    const NEW_CUSTOMER_EMAIL = 'new_customer_email';

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
            Mage::getSingleton('checkout/session')->addError(
                $this->__('The onepage checkout is disabled.')
            );
            $this->_redirect('checkout/cart');

            return;
        }

        $quote = $this->_getQuote();
        $includeCart = $this->_getHelper()->includeCart();

        /**
         * If quote is empty or has errors
         **/
        if (!$quote->hasItems() || $quote->getHasError()) {
            if ($includeCart === false) {
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

        // Needed for coupon codes to work as expected
        $quote->getShippingAddress()
            ->setCollectShippingRates(true);

        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(
            Mage::getUrl(
                '*/*/*',
                array('_secure' => true)
            )
        );

        $this->getOnepage()->initCheckout();

        $this->loadLayout();

        $root = $this->getLayout()->getBlock('root');

        if ($root && (!$quote->hasItems() || $quote->getHasError())) {
            $root->addBodyClass('lessfriction-empty-cart');
        }

        /**
         * Inititalize session messages
         **/
        $this->_initLayoutMessages('customer/session');

        if ($includeCart == true) {
            $this->_initLayoutMessages('checkout/session');
        }

        $this->getLayout()->getBlock('head')->setTitle(
            $this->__('Checkout')
        );

        $this->renderLayout();
    }

    /**
     * Save checkout method
     *s
     *
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
     * Customer email exists action
     *
     * Generates a JSON response.
     *
     * @return void
     */
    public function customerEmailExistsAction()
    {
        $email    = $this->getRequest()->getPost('email', false);
        $result   = array();
        $checkout = Mage::getModel('lessfriction/Type_LessFriction');
        $config   = Mage::getModel('lessfriction/config');
        $websiteId = Mage::app()->getWebsite()->getId();

        if ($email !== false) {
            if ($this->_customerEmailExists($email, $websiteId)
                && $config->switchToGuestCheckoutIfCustomerEmailExists() === true
            ) {
                $result['force_method'] = $checkout::METHOD_GUEST;
            } else {
                $result['force_method'] = 1;
            }
        }

        $this->raiseCustomerEmailEvent($email, $websiteId, $this->getOnepage()->getQuote()->getId());

        $this->_jsonResponse($result);
    }

    /**
     * Check if customer email exists
     *
     * @param string $email     Customer email
     * @param int    $websiteId Website ID
     *
     * @return false|Mage_Customer_Model_Customer
     */
    protected function _customerEmailExists($email, $websiteId = null)
    {
        $customer = Mage::getModel('customer/customer');

        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }

        $customer->loadByEmail($email);

        if ($customer->getId()) {
            return $customer;
        }

        return false;
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

        /**
         * Try to save the address with the posted data
         **/
        $data = $this->getRequest()->getPost('billing', array());
        $addressId = $this->getRequest()->getPost('billing_address_id', false);

        if (isset($data['email'])) {
            $data['email'] = trim($data['email']);
        }

        $result = $this->getOnepage()->saveBilling($data, $addressId);

        /**
         * Get the block relations, load them as JSON and add them
         * to the result
         **/
        $relations = $this->getRequest()->getPost('relations', '');
        $relations = explode(',', $relations);

        $result['blocks'] = $this->_getBlocksAsJson($relations);

        $this->_jsonResponse($result);
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

        /**
         * Try to save the address with the posted data
         **/
        $data = $this->getRequest()->getPost('shipping', array());
        $addressId = $this->getRequest()->getPost('shipping_address_id', false);

        if (isset($data['email'])) {
            $data['email'] = trim($data['email']);
        }

        $result = Mage::getModel('lessfriction/Type_LessFriction')->saveShipping($data, $addressId);

        /**
         * Since shipping address might impact shipping
         * methods we should collect the shipping rates
         * anew
         **/
        $this->getOnepage()->getQuote()
            ->getShippingAddress()->setCollectShippingRates(true);

        /**
         * Get the block relations, load them as JSON and add them
         * to the result
         **/
        $relations = $this->getRequest()->getPost('relations', '');
        $relations = explode(',', $relations);

        $result['blocks'] = $this->_getBlocksAsJson($relations);

        $this->_jsonResponse($result);
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

        $data = $this->getRequest()->getPost('shipping_method', '');
        $result = $this->getOnepage()->saveShippingMethod($data);

        $quote = $this->getOnepage()->getQuote();

        $quote->collectTotals()
            ->save();

        $quote->getShippingAddress()
            ->setCollectShippingRates(true)
            ->collectShippingRates()
            ->save();

        $relations = $this->getRequest()->getPost('relations', '');
        $relations = explode(',', $relations);
        $result['blocks'] = $this->_getBlocksAsJson($relations);

        $this->_jsonResponse($result);
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

        try {
            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);
            $redirectUrl = $this->_getQuote()->getPayment()->getCheckoutRedirectUrl();

            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            // Add exception to result data
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }

            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            // Add exception to result data
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            /**
             * Unable to set payment method, make sure
             * to log the exception
             **/
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set payment method.');
        }

        $relations = $this->getRequest()->getPost('relations', '');
        $relations = explode(',', $relations);

        $result['blocks'] = $this->_getBlocksAsJson($relations);

        $this->getOnepage()->getQuote()->collectTotals()->save();
        $this->_jsonResponse($result);
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

        $result = array();
        $this->getOnepage()->getQuote()->collectTotals();

        try {
            /**
             * Assure that the terms and conditions have been read
             */
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();

            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);

                /**
                 * Sorry for this hack. Since IE8 failes to get all information we need to solve it
                 * this way until Andreas is back.
                 */
                $_POST['agreement'] = '1';
                $diff = false;

                if ($diff) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__(
                        'Please agree to all the terms and conditions before'
                        . ' placing the order.'
                    );
                    $this->_jsonResponse($result);

                    return;
                }
            }

            /**
             * Import payment data to quote
             */
            $paymentData = $this->getRequest()->getPost('payment', false);

            if ($paymentData) {
                $this->getOnepage()->getQuote()->getPayment()->importData($paymentData);
            }

            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error'] = false;
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }

        $this->getOnepage()->getQuote()->save();

        /**
         * Comment from original onepage checkout code:
         *
         * When there is redirect to third party, we don't want to save the order yet.
         * We will save the order in return action.
         *
         * Our comment to that comment:
         *
         * This is bs, the order is saved above – but we want to redirect
         * the user with the javascript part.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->_jsonResponse($result);
    }

    /**
     * Genereate JSON response
     *
     * @param array $data Block informtion
     *
     * @return void
     */
    protected function _jsonResponse($data = array())
    {
        $jsonData = Mage::helper('core')->jsonEncode($data);
        $this->getResponse()->setHeader('Content-type', 'application/json');
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
     *
     * @todo Handle succes page in a better way
     */
    public function addActionLayoutHandles()
    {
        parent::addActionLayoutHandles();

        if ($this->_getHelper()->isActive() == true
            && $this->getFullActionName() != "checkout_onepage_success"
        ) {
            $layoutUpdate = $this->getLayout()->getUpdate();

            $layoutUpdate->addHandle(self::LAYOUT_HANDLE_BASE);
            $layoutUpdate->addHandle(self::LAYOUT_HANDLE_DEFAULT);
        }

        return $this;
    }

    /**
     * Get blocks as JSON
     *
     * @param array $blockNames Array with block names
     *
     * @return array
     */
    protected function _getBlocksAsJson($blockNames)
    {
        $response = array();
        $layout = $this->getLayout();
        $update = $layout->getUpdate();

        $update->load('lessfriction_json');

        $layout->generateXml();
        $layout->generateBlocks();

        foreach ($blockNames as $blockName) {
            if ($layout->getBlock($blockName)) {
                $response[$blockName] = $layout->getBlock($blockName)->toHtml();
            }
        }

        return $response;
    }

    /**
     * @param $email
     * @param $websiteId
     * @param $quoteId
     */
    protected function raiseCustomerEmailEvent($email, $websiteId, $quoteId)
    {
        $data = array('address' => $email, 'site' => $websiteId, 'quote' => $quoteId);
        Mage::dispatchEvent(self::NEW_CUSTOMER_EMAIL, $data);
    }
}
