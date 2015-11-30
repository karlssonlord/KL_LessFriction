<?php

/**
 * @author David Wickström <david@karlssonlord.com>
 */
class KL_LessFriction_Model_Shipping_MethodProvider
{
    /**
     * @var array
     */
    protected $rates = array();

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function setShippingMethod(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        if (Mage::getModel('lessfriction/config')->preselectCheapestShippingMethod()) {
            return $this->setCheapestMethod($quote);
        }

        return $this;
    }

    /**
     * @param $quote
     * @return mixed
     */
    private function setCheapestMethod($quote)
    {
        if ($quote->getShippingAddress()->getShippingMethod()) return $quote;

        $cheapestMethod = $this->getCheapestMethod($quote);

        if (!$cheapestMethod) return $quote;

        return $quote->getShippingAddress()->setShippingMethod($cheapestMethod->getCode());
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return mixed
     */
    private function getCheapestMethod(Mage_Sales_Model_Quote $quote)
    {
        $rates = $quote->getShippingAddress()->getGroupedAllShippingRates();

        foreach ($rates as $code => $groupItems) {
            foreach ($groupItems as $rate) {
                $this->addIfValid($rate);
            }
        }

        $rates = $this->ratesSortedByPrice();

        return $this->getCheapestNonFreeMethod($rates);
    }

    /**
     * @param Mage_Sales_Model_Quote_Address_Rate $rate
     */
    private function addIfValid(Mage_Sales_Model_Quote_Address_Rate $rate)
    {
        $this->rates[$rate->getPrice()] = $rate;
    }

    /**
     * @return array
     */
    private function ratesSortedByPrice()
    {
        ksort($this->rates);

        return $this->rates;
    }

    /**
     * @param $rates
     * @return mixed
     */
    private function getCheapestNonFreeMethod(array $rates)
    {
        foreach ($rates as $rate) {
            if ($rate->getPrice() > 0) {
                return $rate;
            }
        }

        /**
         * Finally if for some reason only free shipping is available,
         * then go ahead and return it
         */
        return reset($rates);
    }

}
