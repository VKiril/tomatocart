<?php

class FeedDialog
{
    public $geoZones ;
    public $productsColumns ;
    public $productsAttributes ;
    public $feedConfig ;
    public $tableItems ;
    public $attributesExtra = array(
        'SHIPPING_COD'=>'••  Shipping Cod',
        'SHIPPING_OUTSIDE_EU' => '•• shipping outside the EU',
        'SHIPPING_FOR_AUSTRALIA' => '•• Shipping costs for paypal Austria',
        'SHIPPING_CASH_DELIVERY' => '•• Shipping cost for Cash on Deliver',
        'SHIPPING_CREDIT_CARD' => '•• Shipping cost for Creditcard',
        'SHIPPING_COST_PAYPAL' => '•• Shipping costs for paypal',
        'SHIPPING_PAYPAL' => '•• Shipping paypal',
        'SHIPPING_READY_TRANSFER' => '•• Shipping costs Ready for Transfer',
        'SHIPPING_ELV' => '•• Shipping costs ELV',
        'SHIPPING_PURCHASE_ORDERS' => '•• Shipping costs for purchase orders',
        'SHIPPING_MONEYBOOKERS' => '•• Shipping costs at Moneybookers',
        'SHIPPING_CLICK_BY' => '•• Shipping costs Click & Buy',
        'SHIPPING_GIROPAY' => '•• Shipping costs Giropay',
        'SHIPPING_DEBIT' => '•• Shipping Debit',
        'SHIPPING_ACCOUNT' => '•• Shipping Account',

        'SHIPPING'=>'•• Shipping',
        'SHIPPING_COST' => '•• Shipping Cost (Default)',
        'SHIPPING_ADDITION'=>'•• Shipping Addition'
    );
    public $exportConfig = array(
        'TAX_RATE' => '••• Tax Rate (Default)',
        'COUPON'=>'••• Coupon',
        'SIZE' => '••• Size',
        'COLOR' => '••• Color',
        'GENDER' => '••• Gender',
        'MATERIAL' => '••• Material',
        'EAN' => '••• Ean',
        'GOOGLE' => '••• Google',
        'ISBN' => '••• ISBN',
        'BASE_UNIT' => '••• Base Unit',
        'BASE_PRICE' => '••• Base Price',
        'YATEGOO_CATEGORY' => '••• Yategoo Category',
        'UVP' => '••• Manufacturer recommended price'
    );

    public function __construct()
    {
        $this->getFeedConfig();
        $this->getGeoZones();
        $this->getProductsAttributes();
        $this->getProductsColumns();
    }

    private function getGeoZones()
    {
        global $osC_Database;
        $var = $osC_Database->query("
          select gz.geo_zone_id, gz.geo_zone_name from :geo_zone_table gz
      ");
        $var->bindTable(':geo_zone_table', TABLE_GEO_ZONES);
        $var->execute();
        $geo_zones = $this->fetch($var);
        $this->geoZones->$geo_zones;
    }

    private function getProductsColumns()
    {
        global $osC_Database;
        $var = $osC_Database->query("
            SELECT DISTINCT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '" . TABLE_PRODUCTS . "'
        ");
        $var->execute();
        $buff = $this->fetch($var);
        $productsColumns = array();
        $productsColumns[] = '--empty--';
        foreach ($buff as $key => $value) {
            $productsColumns[] = $value['COLUMN_NAME'];
        }
        $this->productsColumns = $productsColumns;
    }

    private function getProductsAttributes()
    {
        global $osC_Database;
        $var = $osC_Database->query("
            SELECT  DISTINCT pav.name
            FROM " . TABLE_PRODUCTS_ATTRIBUTES_VALUES . " pav
        ");
        $var->execute();
        $buff = $this->fetch($var);
        $productsAttributes = array();
        $productsAttributes[] = '--empty--';
        foreach ($buff as $elements) {
            $productsAttributes[] = $elements['name'];
        }
        $this->productsAttributes = $productsAttributes ;
    }

    private function getFeedConfig()
    {
        global $osC_Database;
        $var = $osC_Database->query("
        SELECT c.configuration_key, c.configuration_value
        FROM :configuration c
        WHERE c.configuration_key LIKE '%FEED%'
        ");
        $var->bindTable(':configuration', TABLE_CONFIGURATION);

        $var->execute();
        $buff = $this->fetch($var);
        $feed_conf = array();
        foreach ($buff as $elements) {
            $feed_conf[$elements['configuration_key']] = $elements['configuration_value'];
        }

        $this->feedConfig = $feed_conf;
    }

    /**
     * @param $field
     * @return string
     */
    public function getValue($field)
    {
        if(isset($this->feedConfig[$field])){
            return $this->feedConfig[$field] ;
        } else {
            return "";
        }
    }

    /**
     * @param $item
     * @return string
     */
    public function getProductTableItems($item)
    {
        $tableItems = "";
        foreach ($this->productsColumns as $key => $value) {

            if ($this->feedConfig[$item] == $value) {
                $tableItems .= "
                    '<option selected > " . $value . " </option>'+

                ";
            } else {
                $tableItems .= "
                    '<option > " . $value . " </option>'+
                ";
            }
        }

        return $tableItems;
    }

    /**
     * @param $item
     * @return string
     */
    public function getProductsAttributesItems($item)
    {
        $tableItems = "";
        foreach ($this->productsAttributes as $key => $value) {

            if ($this->feedConfig[$item] == $value) {
                $tableItems .= "
                    '<option selected > " . $value . " </option>'+

                ";
            } else {
                $tableItems .= "
                    '<option > " . $value . " </option>'+
                ";
            }
        }

        return $tableItems;
    }

    /**
     * @param $element
     * @return string
     */
    public function getElement($element)
    {
        $result = "";
        $feed_conf = $this->feedConfig;
        if ($feed_conf['FEED_CONDITION_1'] == $element) {
            $result = "selected";
        }
        return $result;
    }

    /**
     *
     * the same steps like at symfony
     */
    function fetch($query){
        global $osC_Database;
        $temp = array();
        $temp[] = $query->toArray();
        do {
            $buff = $query->next();
            if($buff){
                $temp[] = $buff ;

            } else {
                break ;
            }
        } while ($buff);

        return $temp ;
    }
}