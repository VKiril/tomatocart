<?php
require('PluginResources.php');

class FeedConnector implements FeedPlugin {

    protected $categoryPath;
    protected $qFields;
    protected $taxZone;
    protected $container;
    protected $categoryParent;
    protected $deliveryTime;
    protected $tempContentsOfCart;
    protected $shippingModule;
    protected $productsDataQuery;
    protected $osC_Database ;
    protected $productResources ;
    public $feedRows = array() ;
    public $osC_Products ;
    public $categories ;
    public $attributes ;


    /**
     * constructor caller is forwarded
     *
     * @param Feed $container
     */
    public function __construct(Feed $container)
    {
        global $osC_Database ;
        $this->container = $container;
        $this->osC_Database = $osC_Database ;
        $this->getFeedElements();
        $this->getAllCategories();
        $helper = new PluginResources();
        $this->attributes = $helper->getAttributes();
    }

    /**
     * Returns APIUsername
     * @return string
     */
    public function getApiUsername()
    {
        return $this->_getConfig('FEED_USER');
    }

    /**
     * Return APIPassword
     * @return string
     */
    public function getApiPassword()
    {
        return $this->_getConfig('FEED_PASSWORD');
    }

    /**
     * Returns APISecret code
     * @return string
     */
    public function getApiSecret()
    {
       return $this->_getConfig('FEED_SECRET');
    }


    /**
     * @return mixed
     */
    public function getShopName()
    {
        return $this->_getConfig('STORE_NAME');
    }

    //select from table configuration, configuration value where key = configName
    protected function _getConfig($configName) {
        $config = $GLOBALS['osC_Database']->query(
            "SELECT configuration_value FROM " . TABLE_CONFIGURATION . "
             WHERE configuration_key = '".$configName."'"
        );
        $config->execute();

        return $config->value('configuration_value');
    }

    /**
     * Returns posible shop configuration option for different channels
     * @return stdClass
     */
    public function getShopConfig()
    {
        $oReturn = new stdClass();
        $oReturn->langid = $this->_getShopLanguageConfig();
        $oReturn->currency = $this->_getShopCurrencyConfig();
        $oReturn->status = $this->getShopCondition();

        return $oReturn;
    }

    /**
     * @return stdClass
     */
    protected function _getShopLanguageConfig()
    {
        $oConfig = new stdClass();
        $aLanguages = $this->_getLanguagesOrCurrenciesArray('languages');
        $oConfig->key = "lang";
        $oConfig->title = "Language";
        foreach ($aLanguages as $language) {
            $oValue = new stdClass();
            $oValue->key = $language['id'];
            $oValue->title = $language['name'];
            $oConfig->values[] = $oValue;
        }

        return $oConfig;
    }

    /**
     * @return stdClass
     */
    protected function _getShopCurrencyConfig()
    {
        $oConfig = new stdClass();
        $aCurrencies =  $this->_getLanguagesOrCurrenciesArray('currencies');
        $oConfig->key = "currency";
        $oConfig->title = "Currency";
        foreach($aCurrencies as $oCurrency) {
            $oValue = new stdClass();
            $oValue->key = $oCurrency['id'];
            $oValue->title = $oCurrency['title'];
            $oConfig->values[] = $oValue;
        }

        return $oConfig;
    }

    /**
     * @return stdClass
     */
    public function getShopCondition()
    {
        $values = array(
            0 => 'export_all_products',
            1 => 'export_active_products',
            2 => 'export_products_in_stock',
            3 => 'export_active_products_in_stock',
        );

        $stdConfig = new stdClass();
        $stdConfig->key = 'status';
        $stdConfig->title = 'status';
        foreach ($values as $key => $title) {
            $stdValue = new stdClass();
            $stdValue->key = $key;
            $stdValue->title = $title;
            $stdConfig->values[] = $stdValue;
        }

        return $stdConfig;
    }

    /**
     * @param $instance
     * @return array|string
     */
    protected function _getLanguagesOrCurrenciesArray($instance)
    {
        if($instance == 'languages'){
            $i = 0; $output = array(); $temp = array();

            $data = $GLOBALS['osC_Database']->query( "SELECT languages_id , name FROM " . TABLE_LANGUAGES );
            $data->execute();

            while($i < $data->numberOfRows()) {

                $temp['id']   = $data->value('languages_id');
                $temp['name'] = $data->value('name');
                $output[] = $temp;
                $data->next();
                $i++;

            }

            return $output;

        } else if($instance === 'currencies') {
            $i = 0; $output = array(); $temp = array();

            $data = $GLOBALS['osC_Database']->query( "SELECT currencies_id , title FROM " . TABLE_CURRENCIES );
            $data->execute();

            while($i < $data->numberOfRows()) {

                $temp['id']   = $data->value('currencies_id');
                $temp['title'] = $data->value('title');
                $output[] = $temp;
                $data->next(); $i++;

            }

            return $output;
        } else {

            return 'Illegal instance!';
        }
    }

    protected function _iniParameters()
    {
        $this->shippingModule = $this->_getConfig('FEED_SHIPPING_METHOD');
        $this->taxZone      = $this->_getTaxZoneAndCountry();
        $this->deliveryTime = $this->_getDeliveryTime();
    }


    public function get_osC_Products()
    {
        include('/includes/classes/products.php');
        global $current_category_id, $osC_Product;

        $osC_Products = new osC_Products($current_category_id);
        $Qlisting = $osC_Products->execute();
        $temp = array();
        while ($Qlisting->next()) {
            $temp[] = new osC_Product($Qlisting->value('products_id'));
        }
        $products = array();
        foreach ($temp as $item1) {
            $products[$item1->_data['id']] = $item1->_data;
        }
        $this->osC_Products = $products ;
    }

    /**
     * Generates and returns the array of datafeed
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @return stdClass
     */
    public function getFeed(stdClass $queryParameters, array $fieldMap)
    {
        error_reporting(0);
        $this->_iniParameters();
        $time = microtime(true);
        $limit  = 5;
        $offset = 0;

        //save user's cart contents in scope to operate with
        //global ShoppingCart object for get delivery price
        $this->_saveCartContents();

        header('Content-Encoding: UTF-8');
        header("Content-type: text/csv; charset=UTF-8");
        header('Content-Disposition: attachment; filename=feedExport.csv');
        mb_internal_encoding("UTF-8");
        $csv_file = fopen("php://output", 'w+');

        if(!$csv_file) { echo 'File Error'; exit(); }
        fputcsv($csv_file, array_keys($fieldMap), ';', '"');
        $this->get_osC_Products();

        $helper = new PluginResources();
        do{
            $this->productResources = $helper->getProductsResources($queryParameters, $limit, $offset);
            $count = 0 ;
            foreach ($this->productResources['products'] as $item) {
                if(array_key_exists($item['products_id'], $this->productResources['allCombinations'])){

                    foreach ($this->productResources['allCombinations'][$item['products_id']] as $element) {
                        $row = null;
                        $row = $this->_getFeedRow($fieldMap, $this->productResources['attrValues'],$queryParameters, $item['products_id'], $element) ;
                        fputcsv($csv_file, $row, ';', '"');
                    }
                } else {
                    $row = $this->_getFeedRow($fieldMap, $this->productResources['attrValues'], $queryParameters, $item['products_id'], null) ;
                    fputcsv($csv_file, $row, ';', '"');
                }
                $count ++;
            }
            $offset += $limit;

        }while(sizeof($this->productResources['products']) == 5  );

        //echo microtime(true) - $time;

        //restoring current contents after influence on
        //ShopCart in scope to calculate shipping cost
        $this->_restoreCartContents();
        fclose($csv_file);
    }

    /**
     * @param $fieldMap
     * @param $oArticle
     * @param $queryParameters
     * @param $id
     * @param $element
     * @return array
     */
    protected function _getFeedRow($fieldMap, $oArticle, $queryParameters, $id, $element)
    {
        $row = array();
        foreach($fieldMap as $key => $value) {
            $row[$key] = str_replace(
                array("\r", "\r\n", "\n"), '',
                mb_convert_encoding($this->_getFeedColumnValue($value, $oArticle, $queryParameters, $id, $element), 'UTF-8')
            );
        }

        return $row;
    }

    /**
     * @param $field
     * @param $attrList
     * @param $queryParameters
     * @param $productId
     * @param $attr
     * @return mixed|null|string
     */
    protected function _getFeedColumnValue($field, $attrList, $queryParameters, $productId, $attr)
    {
        $productResource = $this->productResources['products'][$productId];
        $result = null ;
        switch($field) {
            case 'ModelOwn':
                $result = $this->getModelOwn($productId, $attrList, $attr);
                break ;
            case 'Name':
                $result = $productResource['products_name'];
                break;
            case 'Subtitle':
                $result =  $productResource['products_short_description'];
                break;
            case 'Description':
                $result = strip_tags($productResource['products_description']);
                $result = str_replace('?', ',', $result);
                break;
            case 'AdditionalInfo':
                $result = $this->_getLink($productResource);
                break;
            case 'ProductsVariant':
                $result = $this->getProductVariant($attr);
                break;
            case 'Image':
                $result = $this->_getImage($productResource['images']);
                break;
            case 'Manufacturer':
                $result = $productResource['manufacturers_name'];
                break;
            case 'Model':
                $result = $productResource['products_model'];
                break;
            case 'Category':
                $result = $this->getProductCategory($productId);
                break;
            case 'CategoriesGoogle':
                $result = $this->get3RowFeedElementValue("GOOGLE", $productId);
                break;
            case 'CategoriesYatego':
                $result = $this->get3RowFeedElementValue("YATEGOO_CATEGORY", $productId);
                break;
            case 'ProductsEAN':
                $result = $this->get3RowFeedElementValue("EAN", $productId);
                break;
            case 'ProductsISBN':
                $result = $this->get3RowFeedElementValue("EAN", $productId);
                break;
            case 'Productsprice_brut':
                $price = $productResource['products_price'];
                $var = $this->_getTaxRate($this->productResources['products'][$productId]);
                $result = ($price / 100 * $var ) + $price ;
                break;
            case 'Productspecial':
                $result = $this->_getSpecialPrice($productResource);
                break;
            case 'Productsprice_uvp':
                $result = $this->get3RowFeedElementValue("UVP", $productId);
                break;
            case 'Weight':
                $result = $productResource['products_weight'];
                break;
            case 'BasePrice':
                $result = $productResource['products_price'];
                break;
            case 'BaseUnit':
                $result = $result = $this->get3RowFeedElementValue("BASE_UNIT", $productId);
                break;
            case 'Productstax':
                return $this->_getTaxRate($productResource);
                break;
            case 'Currency':
                $result = $queryParameters->currency ;
                break;
            case 'Quantity':
                $result = $productResource['products_quantity'];
                break;
            case 'DeliveryTime':
                $result = $this->getDeliveryTime();
                break;
            case 'AvailabilityTxt':
                $result = $productResource['products_status'];
                break;
            case 'Condition':
                if($this->feedRows['FEED_CONDITION_1'] != '--empty--'){
                    $result = $this->feedRows['FEED_CONDITION_1'];
                } else {
                    $result = $this->feedRows['FEED_CONDITION_2'];
                }
                break;
            case 'Coupon':
                $prod = new PluginResources();
                $result = $prod->getCoupon($productId);
                break;
            case 'Gender':
                $result = $this->get3RowFeedElementValue("GENDER", $productId);
                break;
            case 'Size':
                $result = $this->get3RowFeedElementValue("SIZE", $productId);
                break;
            case 'Color':
                $result = $this->get3RowFeedElementValue("COLOR", $productId);
                break;

            case 'Material':
                $result = $this->get3RowFeedElementValue("MATERIAL", $productId);
                break;
            case 'Packet_size':
                $result = $this->getPacketSize($productResource);
                break;
            case 'Shipping':
                $result = $this->get2RowFeedElementValue("SHIPPING", $productId);
                break;
            case 'ShippingAddition':
                $result = $this->get2RowFeedElementValue("SHIPPING_ADDITION", $productId);
                break;
            case 'shipping_cod':
                $result = $this->get2RowFeedElementValue("SHIPPING_COD", $productId);
                break;
            case 'shipping_credit':
                $result = $this->get2RowFeedElementValue("SHIPPING_CREDIT_CARD", $productId);
                break;
            case 'shipping_paypal':
                $result = $this->get2RowFeedElementValue("SHIPPING_PAYPAL", $productId);
                break;
            case 'shipping_paypal_ost':
                $result = $this->get2RowFeedElementValue("SHIPPING_COST_PAYPAL", $productId);
                break;
            case 'shipping_transfer':
                $result = $this->get2RowFeedElementValue("SHIPPING_READY_TRANSFER", $productId);
                break;
            case 'shipping_debit':
                $result = $this->get2RowFeedElementValue("SHIPPING_DEBIT", $productId);
                break;
            case 'shipping_account':
                $result = $this->get2RowFeedElementValue("SHIPPING_ACCOUNT", $productId);
                break;
            case 'shipping_moneybookers':
                $result = $this->get2RowFeedElementValue("SHIPPING_MONEYBOOKERS", $productId);
                break;
            case 'shipping_click_buy':
                $result = $this->get2RowFeedElementValue("SHIPPING_CLICK_BY", $productId);
                break;
            case 'shipping_giropay':
                $result = $this->get2RowFeedElementValue("SHIPPING_GIROPAY", $productId);
                break;
            case 'shipping_comment':
                $result  = $this->feedRows['FEED_COMMENT'];
                break;

            default:
                if (isset($this->productResources[$productId][$field])) {

                    return $this->productResources[$productId][$field];
                } else {

                    return '';
                }
        }

        return $result ;
    }

    /**
     * @param $productResource
     * @return mixed|string
     */
    protected function getPacketSize($productResource)
    {
        if(array_key_exists('FEED_PACKET_SIZE_1', $this->feedRows) ){
            if(array_key_exists($this->feedRows['FEED_PACKET_SIZE_1'], $this->productResources)){
                return $productResource[$this->feedRows['FEED_PACKET_SIZE_1']];
            } else {
                return "";
            }
        }

        if(array_key_exists('FEED_PACKET_SIZE_2', $this->feedRows) ){
            if(array_key_exists($this->feedRows['FEED_PACKET_SIZE_2'], $this->productResources)){
                return $productResource[$this->feedRows['FEED_PACKET_SIZE_2']];
            } else {
                return "";
            }
        }

        if( array_key_exists('FEED_PACKET_SIZE_LENGTH', $this->feedRows)
            and
            array_key_exists('FEED_PACKET_SIZE_WIDTH', $this->feedRows)
            and
            array_key_exists('FEED_PACKET_SIZE_HEIGHT', $this->feedRows) ){


            return str_replace(" ", '',
                $this->feedRows['FEED_PACKET_SIZE_LENGTH'].'x'.
                $this->feedRows['FEED_PACKET_SIZE_WIDTH'].'x'.
                $this->feedRows['FEED_PACKET_SIZE_HEIGHT'].'cm');
        }
    }

    /**
     * @return string
     */
    protected function getDeliveryTime()
    {
        $deliveryTimeType = array('days' =>'D','months'=>'M','weeks'=>'W', );
        $from = $this->feedRows['FEED_DTIME_FROM'];
        $to = $this->feedRows['FEED_DTIME_TO'] ;
        $type = $this->feedRows['FEED_DTIME_TYPE'];
        $result =  $from.'_'.$to.'_'.$deliveryTimeType[$type];

        return $result;
    }

    /**
     * @param $element
     * @param $id
     * @return string
     */
    protected function get3RowFeedElementValue($element, $id)
    {

        if(array_key_exists('FEED_'.$element.'_1', $this->feedRows) ){
            if(array_key_exists($this->feedRows['FEED_'.$element.'_1'], $this->productResources)){
                return $this->productResources['products'][$id][$this->feedRows['FEED_'.$element.'_1']];
            } else {
                return "";
            }
        }

        if(array_key_exists('FEED_'.$element.'_2', $this->feedRows) ){
            if(array_key_exists($this->feedRows['FEED_'.$element.'_2'], $this->productResources)){
                return $this->productResources['products'][$id][$this->feedRows['FEED_'.$element.'_2']];
            } else {
                return "";
            }
        }
        if(array_key_exists('FEED_'.$element.'_3', $this->feedRows) ){
            return $this->feedRows['FEED_'.$element.'_3'];
        }
    }

    /**
     * @param $element
     * @param $id
     * @return string
     */
    protected function get2RowFeedElementValue($element, $id)
    {
        if(array_key_exists('FEED_'.$element.'_1', $this->feedRows) ){
            if(array_key_exists($this->feedRows['FEED_'.$element.'_1'], $this->productResources)){
                return $this->productResources['products'][$id][$this->feedRows['FEED_'.$element.'_1']];
            } else {
                return "";
            }
        }

        if(array_key_exists('FEED_'.$element.'_2', $this->feedRows) ){
            return $this->feedRows['FEED_'.$element.'_2'];
        }
    }

    /**
     * @param $prodId
     * @return mixed
     */
    protected function getProductCategory($prodId)
    {
        $result = $this->categories[$this->osC_Products[$prodId]['category_id']]['categories_name'];
        return $result ;
    }

    /**
     * @param $attr
     * @return string
     */
    protected function getProductVariant($attr)
    {
        $result = array();
        if($attr == null)
            return '';

        foreach ($attr as $key=>$value) {
            if(is_numeric($key)){
                $key = $this->attributes[$key]['name'];
            }
            $result[] = $key ;
        }

        return implode('|', $result);
    }

    /**
     * @param $prodId
     * @param $attributesList
     * @param $attributes
     * @return string
     */
    protected function getModelOwn($prodId, $attributesList, $attributes)
    {
        $modelOwn = $prodId;
        if($attributes == null){
            return $modelOwn;
        }

        foreach ($attributes as $value) {
            if(is_numeric($value)){
                $modelOwn .= '_'.$value ;
            }
             else{
                 $modelOwn .= '_'.$attributesList[$value] ;
             }

        }

        return $modelOwn ;
    }

    /**
     * @param $oArticle
     * @return int
     */
    protected function _getTaxRate($oArticle)
    {

        $tax_class = $oArticle['products_tax_class_id'] ;

        $taxRate = $this->osC_Database->query('
            SELECT
                tr.tax_rate
            FROM '.TABLE_TAX_RATES.' tr
            WHERE tr.tax_class_id = '.$tax_class.'
        ');
        $taxRate->execute();
        $taxRate = $this->fetch($taxRate);
        $finalTaxValue = 0 ;
        foreach ($taxRate as $item) {
            $finalTaxValue += $item['tax_rate'];
        }

        return $finalTaxValue ;
    }

    /**
     * @return string
     */
    protected function _getDeliveryTime()
    {
        $return = "";
        if(array_key_exists('FEED_DTIME_FROM', $this->feedRows) and array_key_exists('FEED_DTIME_TO', $this->feedRows)){
            $return = $this->feedRows['FEED_DTIME_FROM'].'_'
                .$this->feedRows['FEED_DTIME_TO'].'_'
                .$this->feedRows['FEED_DTIME_TYPE'];
        }

        return $return;
    }

    /**
     * getDeliveryCost
     * return DeliveryCost of an product
     * @param $row -- info about one product
     * @return float|string
     */
    protected function _getDeliveryCost( $row )
    {
        $GLOBALS['osC_ShoppingCart']->add(strtok($row['id'],'_'));

        if(strstr($row['id'], '_')) {
            $_SESSION['osC_ShoppingCart_data']['contents'][strtok($row['id'],'_')]['price'] = $row['pvar_price'];
            $_SESSION['osC_ShoppingCart_data']['contents'][strtok($row['id'],'_')]['weight'] = $row['pvar_weight'];
            $_SESSION['osC_ShoppingCart_data']['contents'][strtok($row['id'],'_')]['final_price'] = $row['pvar_price'];
            $_SESSION['osC_ShoppingCart_data']['contents'][strtok($row['id'],'_')]['tax_class_id'] = $row['tax_class_id'];
            $GLOBALS['osC_ShoppingCart']->_calculate();
        }

        foreach($_SESSION['osC_ShoppingCart_data']['shipping_quotes'] as $quote){
            if($quote['module'] == $this->shippingModule){
                $cheapestPrice = $quote['methods'][0]['cost'];
                break;
            }
        }



        $GLOBALS['osC_ShoppingCart']->remove(strtok($row['id'],'_'));

        if($cheapestPrice == null) {

            return 0;
        } else {

            return $cheapestPrice;
        }
    }

    protected function _saveCartContents()
    {
        if($GLOBALS['osC_ShoppingCart']->_contents) {
            $this->tempContentsOfCart = $GLOBALS['osC_ShoppingCart']->_contents;
            $GLOBALS['osC_ShoppingCart']->_contents = array();
        }
    }

    protected function _restoreCartContents()
    {
        if($this->tempContentsOfCart) {
            $GLOBALS['osC_ShoppingCart']->_contents = $this->tempContentsOfCart;
            $GLOBALS['osc_ShoppingCart']->_calculate();
        }
    }


    /**
     * @param int $orderIdIsSet
     * @return array
     * this function is used for the next usage: Example: $data->value($queryArray[$value])
     * to work with db object
     */
    protected function _getQueryFields($orderIdIsSet = 0)
    {
        $queryArray = array(
            'id' => 'id',
            'weight_rule' => 'weight_rule',
            'quantity' => 'quantity',
            'model' => 'model',
            'price' => 'price',
            'weight' => 'weight',
            'weight_class' => 'weight_class',
            'status' => 'status',
            'manufacturer' => 'manufacturer',
            'categories_id' => 'categories_id',
            'parent_id' => 'parent_id',
            'tax_class_id' => 'tax_class_id',
            'language_id' => 'language_id',
            'name' => 'name',
            'short_description' => 'short_description',
            'description' => 'description',
            'currencies_code' => 'currencies_code',
            'currencies_decimal_places' => 'currencies_decimal_places',
            'currencies_value' => 'currencies_value',
            'special_price' => 'special_price',
            'coupon' => 'coupon',
            'pvar_id' => 'pvar_id',
            'pvar_price' => 'pvar_price',
            'pvar_status' => 'pvar_status',
            'pvar_model' => 'pvar_model',
            'pvar_qty' => 'pvar_qty',
            'pvar_weight' => 'pvar_weight'
        );

        if($orderIdIsSet) {
            $queryArray[] = 'order_products_quantity';
        }

        return $queryArray;
    }


    /**
     * getSpecialPrice
     *
     * return special price
     * @param $row
     * @return string
     */
    protected function _getSpecialPrice($row)
    {
        $special = $this->osC_Database->query("
            SELECT
                s.specials_new_products_price,
                s.start_date,
                s.expires_date
            FROM
                ".TABLE_SPECIALS." s
            WHERE s.products_id = ".$row['products_id']."
        ");

        $special->execute();
        $special = $this->fetch($special);
        $special = $special[0];
        $result = '';

        if ($special['start_date'] < date('Y-m-d H:i:s') and $special['expires_date'] > date('Y-m-d H:i:s') ){
            $tax = $this->_getPrice($row, true);
            $result = ($tax * $special['specials_new_products_price'] / 100)  + $special['specials_new_products_price'];
        }


        return $result ;
    }


    /**
     * @return mixed
     */
    protected function _getTaxZoneAndCountry()
    {
        $db = $GLOBALS['osC_Database'];
        $temp = $this->_getConfig('FEED_TAX_ZONE');
        $data = $db->query("
            SELECT	zone_id, zone_country_id

            FROM	toc_zones_to_geo_zones

            WHERE	geo_zone_id = ".$temp
        );

        $data->execute();

        $output['zone_id'] = $data->value('zone_id');
        $output['country_id'] = $data->value('zone_country_id');

        return $output;
    }

    /**
     * getPrice
     * BrutePrice and Baseprice are the same
     * @param $brut
     * @param $row
     * @return float
     *
     */

    protected function _getPrice($row, $brut)
    {
        if($this->taxZone['country_id'] && $this->taxZone['zone_id']) {
            $tax = $GLOBALS['osC_Tax']->getTaxRate($row['tax_class_id'], $this->taxZone['country_id'], $this->taxZone['zone_id']);
        } else {
            $tax = $GLOBALS['osC_Tax']->getTaxRate($row['tax_class_id']);
        }
        $tax_class = $row['products_tax_class_id'] ;
        if($tax_class == 0 ){
            return 0 ;
        }
    }

    /**
     *  getImage
     *
     * return url for product image
     *
     * @param $productImage
     * @return string
     */
    protected function _getImage($productImage)
    {
        if($productImage) {

            return 'http://'.$_SERVER['HTTP_HOST']."/images/products/originals/".$productImage[0];
        } else {

            return 'no_image';
        }
    }

    /**
     *  _getWeightKg
     *
     * return weight in kilograms
     *
     * @param $row
     * @return double
     */
    protected function _getWeightKg($row)
    {
        if($row['products_weight_class']) {
            return $row['weight_rule'] * $row['weight'];
        } else {
            return $row['weight'];
        }
    }


    /**
     * @param $product
     * @return string
     */
    protected function _getLink($product)
    {
        return ' http://'.$_SERVER['HTTP_HOST'].'/products.php?'.$product['products_id'];
    }



    /**
     * getParent
     *
     * get parent category id for given category id.
     * @param $catID
     * @return mixed
     */
    protected function _getParent($catID)
    {
        $db = $GLOBALS['osC_Database'];
        $sql = "SELECT parent_id FROM " . TABLE_CATEGORIES . "
                WHERE categories_id = '" . $catID . "'";
        if (isset($this->categoryParent[$catID])) {
            return $this->categoryParent[$catID];
        } else {
            $parent_query = $db->query($sql);
            $parent_query->execute();
            $this->categoryParent[$catID] = $parent_query->value('parent_id');

            return $parent_query->value('parent_id');
        }
    }





    /**
     * Returns the bridge URL throw the Feed is communicating with shop.
     *
     * @return string
     */
    public function getBridgeUrl()
    {
        $host =   $_SERVER['HTTP_HOST'];
        $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
        $file = '/feed.php';
        
        return $protocol.$host.$file;
    }



    /**
     * Returns posible shop fields configuration throw the Feed gets csv fields
     * @return stdClass
     */
    public function getShopFields()
    {
        return $this->_getFeedFields();
    }

    /**
     * Returns product info
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @param string $id
     * @return mixed
     */
    public function getProductInfo(stdClass $queryParameters, array $fieldMap, $id)
    {
        error_reporting(0);

        $this->get_osC_Products();
        $temp = explode('_',$id);
        $id = $temp[0];
        unset ($temp[0]);
        $helper = new PluginResources();
        $this->productResources = $product = $helper->getProductsResources($queryParameters, null, null, $id);

        $modelOwnExist = false ;

        foreach ($product['allCombinations'] as $item1) {
            foreach ($item1 as $item2) {
                foreach ($item2 as $item3) {

                    foreach ($temp as $element) {
                        if($product['attrValues'][$item3] == $element){
                            $modelOwnExist = true;
                            break 2;
                        } else {
                            $modelOwnExist = false;
                        }
                    }
                }
            }
        }

        $fieldMaps = $this->_getFeedFields();
        if($modelOwnExist) {
            $row = $this->_getFeedRow($fieldMaps, $product['attrValues'] ,$queryParameters, $id, $temp) ;
        } else {
            header('HTTP/1.0 404 Not Found');
            $row = false;
        }
        print_r($row);


        return $row ;
    }

    /**
     * @param stdClass $queryParameters
     * @param $id
     * @return mixed
     */
    public function getOrderProducts(stdClass $queryParameters, $id)
    {
        error_reporting(0);
        $helper = new PluginResources();
        $order = $helper->getOrder($id);

        $result = array();
        $i = 0;
        foreach ($order as $item) {
            $result[$i]['ModelOwn'] =  $item['products_id'];
            $result[$i]['Quantity'] =  $item['products_quantity'];
            $result[$i]['BasePrice']=  $item['final_price'];
            $result[$i]['Currency'] =  $queryParameters->currency ;
            $i++;

        }
        print_r($result);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getFeatures()
    {
        $return = array (
            'getShopName',
            'getConfig',
            'getFeed',
            'getFields',
            'getBridgeUrl',
            'getProduct',
            'getOrderProducts',
        );
        return $return;
    }



    protected function _getFeedFields(){
        $fields = array (
            'ModelOwn' => 'ModelOwn',
            'Name' => 'Name',
            'Subtitle' => 'Subtitle',
            'Description' => 'Description',
            'AdditionalInfo' => 'AdditionalInfo',
            'Image' => 'Image',
            'Manufacturer' => 'Manufacturer',
            'Model' => 'Model',
            'Category' => 'Category',
            'CategoriesGoogle' => 'CategoriesGoogle',
            'CategoriesYatego' => 'CategoriesYatego',
            'ProductsEAN' => 'ProductsEAN',
            'ProductsISBN' => 'ProductsISBN',
            'Productsprice_brut' => 'Productsprice_brut',
            'Productspecial' => 'Productspecial',
            'Productsprice_uvp' => 'Productsprice_uvp',
            'BasePrice' => 'BasePrice',
            'BaseUnit' => 'BaseUnit',
            'Productstax' => 'Productstax',
            'ProductsVariant' => 'ProductsVariant',
            'Currency' => 'Currency',
            'Quantity' => 'Quantity',
            'Weight' => 'Weight',
            'AvailabilityTxt' => 'AvailabilityTxt',
            'Condition' => 'Condition',
            'Coupon' => 'Coupon',
            'Gender' => 'Gender',
            'Size' => 'Size',
            'Color' => 'Color',
            'Material' => 'Material',
            'Packet_size' => 'Packet_size',
            'DeliveryTime' => 'DeliveryTime',
            'Shipping' => 'Shipping',
            'ShippingAddition' => 'ShippingAddition',
            'ShippingPaypal_ost' => 'shipping_paypal_ost',
            'ShippingCod' => 'shipping_cod',
            'ShippingCredit' => 'shipping_credit',
            'ShippingPaypal' => 'shipping_paypal',
            'ShippingTransfer' => 'shipping_transfer',
            'ShippingDebit' => 'shipping_debit',
            'ShippingAccount' => 'shipping_account',
            'ShippingMoneybookers' => 'shipping_moneybookers',
            'ShippingGiropay' => 'shipping_giropay',
            'ShippingClick_buy' => 'shipping_click_buy',
            'ShippingComment' => 'shipping_comment'
        );
        return $fields ;
    }

    private function getFeedElements()
    {
        $var = $this->osC_Database->query('
            SELECT
                conf.configuration_key,
                conf.configuration_value

            FROM '.TABLE_CONFIGURATION.' conf
            WHERE conf.configuration_key LIKE "%FEED_%"
        ');
        $var->execute();
        //
        $temp = $this->fetch($var);

        foreach ($temp as $element) {
            $this->feedRows[$element['configuration_key']] = $element['configuration_value'];
        }
    }

    public function getAllCategories()
    {
        $var = $this->osC_Database
            ->query("
                SELECT
                    cat.categories_id ,
                    cat.parent_id ,
                    cd.categories_name

                FROM ".TABLE_CATEGORIES." cat
                INNER JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd ON  cat.categories_id = cd.categories_id

        ");
        $var->execute();
        $result  = $this->fetch($var);

        $categories = array();
        foreach ($result as $item) {
            $categories[$item['categories_id']] = $item ;
        }
        $this->categories = $categories ;
    }

    /**
     * Returns the URL where to get generated DataFeed
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @return string
     */
    public function getFeedUrl(stdClass $queryParameters, array $fieldMap = null)
    {
        // TODO: Implement getFeedUrl() method.
    }

    /**
     * Generates and returns the delta changes array
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @param int $deltaTimestamp
     * @return stdClass
     */
    public function getDelta(stdClass $queryParameters, array $fieldMap, int $deltaTimestamp)
    {
        // TODO: Implement getDelta() method.
    }

    /**
     * Generates and returns the orders
     *
     * @param int $deltaTimestamp
     * @return stdClass
     */
    public function getOrders(int $deltaTimestamp)
    {
        // TODO: Implement getOrders() method.
    }

    /**
     * Returns the url from where to get the article
     *
     * @param int $deltaTimestamp
     * @return string
     */
    public function getOrdersUrl(int $deltaTimestamp)
    {
        // TODO: Implement getOrdersUrl() method.
    }

    /**
     * Returns the bridge URL parameters the Feed is communicating with shop.
     *
     * @return string
     */
    public function getUrlParameters()
    {
        // TODO: Implement getUrlParameters() method.
    }

    /**
     *
     * the same steps like at symfony
     */
    function fetch($query){
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