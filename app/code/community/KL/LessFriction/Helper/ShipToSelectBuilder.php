<?php

class KL_LessFriction_Helper_ShipToSelectBuilder
{
    /**
     * @var null
     */
    private $allowedCountries;
    /**
     * @var null
     */
    private $countryDirectory;

    public function __construct($allowedCountries = null, $countryDirectory = null)
    {
        $this->allowedCountries = $allowedCountries ? : Mage::getStoreConfig('lessfriction/shipping/specificcountry');
        $this->countryDirectory = $countryDirectory ? : Mage::getModel('directory/country');
    }

    /**
     * @param $thisCountry
     * @return string
     */
    public function buildFor($thisCountry)
    {
        $countriesCollection = $this->getAllowedCountries();

        return $this->buildHtmlSelect(strtoupper($thisCountry), $countriesCollection);
    }

    /**
     * @return array
     */
    private function getAllowedCountries()
    {

        $countriesCollection = array();
        foreach (explode(',', $this->allowedCountries) as $countryCode) {
            $countriesCollection[$countryCode] = $this->countryDirectory->loadByCode($countryCode)->getName();
        }
        return $countriesCollection;
    }

    /**
     * @param $thisCountry
     * @param $countriesCollection
     * @return string
     */
    private function buildHtmlSelect($thisCountry, $countriesCollection)
    {
        $select = '
            <select
                name="shipping[country_id]"
                id="shipping:country_id"
                class="validate-select validation-passed"
                title="Country"
                onchange="if(window.shipping)shipping.setSameAsBilling(false);">
        ';
        foreach ($countriesCollection as $code => $country) {
            $select .= sprintf(
                $this->createOptionString($code, $thisCountry),
                $code,
                $country
            );
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * @param $countryCode
     * @param $currentCountryCode
     * @return string
     */
    private function createOptionString($countryCode, $currentCountryCode)
    {
        $countryCode === $currentCountryCode
            ? $option = '<option selected="selected" value="%s">%s</option>'
            : $option = '<option value="%s">%s</option>';

        return trim($option);
    }
}