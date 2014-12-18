<?php

/**
 * This rewrites is done to allow for the country directory helper to get all countries, regardless of which are "allowed"
 *
 * Class KL_LessFriction_Model_Resource_Country_Collection
 */
class KL_LessFriction_Model_Resource_Country_Collection extends Mage_Directory_Model_Resource_Country_Collection
{
    public function loadByStore($store = null, $getAllCountries = null)
    {
        if ($getAllCountries) {
            $allowCountries = $this->getAllCountries();
        } else {
            $allowCountries = explode(',', (string)$this->_getStoreConfig('general/country/allow', $store));
        }
        if (!empty($allowCountries)) {
            $this->addFieldToFilter("country_id", array('in' => $allowCountries));
        }
        return $this;
    }

    /**
     * @return array
     */
    private function getAllCountries()
    {
        $collection = $countryList = Mage::getModel('directory/country')
            ->getResourceCollection()
            ->addFieldToSelect('country_id')
            ->toArray();

        $allowCountries = array();
        foreach ($collection['items'] as $country) {
            $allowCountries[] = $country['country_id'];
        }
        return $allowCountries;
    }
} 

