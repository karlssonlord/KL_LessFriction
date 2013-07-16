<?php
/**
 * Require Mage_Checkout_CartController that is being extended by
 * KL_Checkout_CartController.
 */
require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'CartController.php';

/**
 * Cart controller
 *
 * @package KL_Checkout
 * @author  Andreas Karlsson <andreas@karlssonlord.com>
 */
class KL_LessFriction_CartController extends Mage_Checkout_CartController
{
    /**
     * Initialize coupon
     */
    public function couponPostJsonAction()
    {
        $result = array();

        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $result['redirect'] = Mage::helper('checkout/url')->getCheckoutUrl();
            return $this->_jsonResponse($result);
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();
$error = false;
        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            // $result['redirect'] = Mage::helper('checkout/url')->getCheckoutUrl();
            // return $this->_jsonResponse($result);
        } else {
            try {
                $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
                $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                    ->collectTotals()
                    ->save();

                if (strlen($couponCode)) {
                    if ($couponCode == $this->_getQuote()->getCouponCode()) {
                        $result['messages'][] = array('type' => 'success', 'text' => $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode)));
                    } else {
                        $error = true;
                        $result['messages'][] = array('type' => 'error', 'text' => $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode)));
                    }
                } else {
                    $result['messages'][] = array('type' => 'success', 'text' => $this->__('Coupon code was canceled.'));
                }

            } catch (Mage_Core_Exception $e) {
                $error = true;
                $result['messages'][] = array('type' => 'error', 'text' => $e->getMessage());
            } catch (Exception $e) {
                $error = true;
                $this->_getSession()->addError();
                $result['messages'][] = array('type' => 'error', 'text' => $this->__('Cannot apply the coupon code.'));
                Mage::logException($e);
            }
        }

        if (!$error) {
            $result['blocks'] = $this->_getBlocksAsJson(array('cart','shipping_method','review'));
        }

        return $this->_jsonResponse($result);
    }

    /**
     * Delete shoping cart item action
     *
     * @see Mage_Checkout_CartController::deleteAction()
     */
    public function deleteJsonAction()
    {
        $result = array();

        $id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)
                  ->save();
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot remove the item.'));
                Mage::logException($e);
            }
        }

        if (!$this->_getCart()->getQuote()->hasItems()) {
            $result['redirect'] = Mage::helper('checkout/url')->getCheckoutUrl();
        }

        $result['blocks'] = $this->_getBlocksAsJson(array('cart','shipping_method','review'));

        return $this->_jsonResponse($result);
    }

    /**
     * Update product configuration for a cart item
     *
     * @see Mage_Checkout_CartController::updateItemOptionsAction()
     */
    public function updateItemOptionsJsonAction()
    {
        $cart   = $this->_getCart();
        $id     = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();
        $result = array();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }

        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);

            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $quoteItem->setQty($params['qty'])->save();

            $cart->save();
            $this->_getSession()->setCartWasUpdated(false);

            if ($quoteItem->getHasError()) {
                Mage::throwException($item->getMessage());
            }
            
            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $quoteItem, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
                $type = 'notice';
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                    $type = 'error';
                }
            }

            $result['error'] = 1;
            $result['messages'] = $e->getMessage();
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update the item.'));
            Mage::logException($e);

            $result['error'] = 1;
            $result['messages'] = array('type' => 'error', 'text' => $this->__('Cannot update the item.'));
        }

        $messages = $this->_getQuoteItemMessages($quoteItem);
        $result['messages'] = $messages;

        if ($result['messages']) {
            $result['error'] = 1;
        }

        $result['blocks'] = $this->_getBlocksAsJson(array('cart','shipping_method','review'));

        return $this->_jsonResponse($result);
    }

    /**
     * Update shopping cart data action
     *
     * @see Mage_Checkout_CartController::updatePostAction()
     */
    public function updatePostJsonAction()
    {
        $result = array();
        $updateAction = (string) $this->getRequest()->getParam('update_cart_action');

        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            default:
                $this->_updateShoppingCart();
        }

        $this->_getSession()->setCartWasUpdated(false);

        if ($this->_getCart()->getQuote()->hasItems()) {
            $result['messages'] = array();

            foreach ($this->_getCart()->getQuote()->getAllItems() as $quoteItem) {
                $messages = $this->_getQuoteItemMessages($quoteItem);

                foreach ($messages as $message) {
                    array_push($result['messages'], $message);
                }

                if (count($result['messages']) > 0 && !isset($result['error'])) {
                    $result['error'] = 1;
                }
            }

            $result['blocks'] = $this->_getBlocksAsJson(array('cart','shipping_method','review'));

        } else {
            /* Cart has no items, reload the whole checkout */
            $result['redirect'] = Mage::helper('checkout/url')->getCheckoutUrl();
        }

        return $this->_jsonResponse($result);
    }

    protected function _getBlocksAsJson($blockNames)
    {
        $response = array();
        $layout = $this->getLayout();
        $update = $layout->getUpdate();

        $update->load('lessfriction_json');

        $layout->generateXml();
        $layout->generateBlocks();

        foreach ($blockNames as $blockName) {
            $response[$blockName] = $layout->getBlock($blockName)->toHtml();
        }

        return $response;
    }

    /**
     * Genereate JSON response
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
     * Retrieve item messages
     * Return array with keys
     *
     * text => the message text
     * type => type of a message
     *
     * @see Mage_Checkout_Block_Cart_Item_Renderer::getMessages()
     *
     * @return array
     */
    public function _getQuoteItemMessages($quoteItem)
    {
        $messages = array();

        // Add basic messages occuring during this page load
        $baseMessages = $quoteItem->getMessage(false);
        if ($baseMessages) {
            foreach ($baseMessages as $message) {
                $messages[] = array(
                    'text' => $message,
                    'type' => $quoteItem->getHasError() ? 'error' : 'notice'
                );
            }
        }

        // Add messages saved previously in checkout session
        $checkoutSession = $this->_getSession();
        if ($checkoutSession) {
            /* @var $collection Mage_Core_Model_Message_Collection */
            $collection = $checkoutSession->getQuoteItemMessages($quoteItem->getId(), true);
            if ($collection) {
                $additionalMessages = $collection->getItems();
                foreach ($additionalMessages as $message) {
                    /* @var $message Mage_Core_Model_Message_Abstract */
                    $messages[] = array(
                        'text' => $message->getCode(),
                        'type' => ($message->getType() == Mage_Core_Model_Message::ERROR) ? 'error' : 'notice'
                    );
                }
            }
        }

        return $messages;
    }
}
