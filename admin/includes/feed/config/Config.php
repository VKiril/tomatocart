<?php


class Config {



    /**
     * @param $configName
     * @return string
     */

    //get the user data from database, example : getConfig('FEED_PASSWORD')
    public function getConfig($configName)
    {
        $config = $GLOBALS['osC_Database']->query(
            "SELECT configuration_value FROM " . TABLE_CONFIGURATION . "
             WHERE configuration_key = '".$configName."'"
        );
        $config->execute();

        return $config->value('configuration_value');
    }

    //is valid only for tracking pixel
    //because no full set of data for feed_wrong
    public function getOrderProductsVariants($orderId)
    {
        $output = array();

        $productsVariants = $GLOBALS['osC_Database']->query("
            SELECT	pv.products_variants_id,
	                opv.products_variants_values_id,
	                opv.orders_products_id,
	                top.products_id

            FROM	toc_products_variants pv

            LEFT JOIN ".TABLE_PRODUCTS_VARIANTS_ENTRIES." pve
            ON (pve.products_variants_entries_id = pv.products_variants_id)

            LEFT JOIN ".TABLE_ORDERS_PRODUCTS_VARIANTS." opv
            ON (opv.products_variants_values_id = pve.products_variants_values_id)

            LEFT JOIN ".TABLE_ORDERS_PRODUCTS." top
            ON (top.orders_products_id = opv.orders_products_id)

            WHERE opv.orders_id = ".$orderId
        );

        $nRows = $productsVariants->numberOfRows();
        $productsVariants->execute();

        while($nRows --){
            $output[$productsVariants->value('products_id').'_'.$productsVariants->value('products_variants_id')] = array(
                'product_id'  => $productsVariants->value('products_id'),
                'variant_id'  => $productsVariants->value('products_variants_id'),
                'order_product_id' => $productsVariants->value('orders_products_id'),
                'products_variants_values_id' => $productsVariants->value('products_variants_values_id'),
            );
            $productsVariants->next();
        }

        return $output;
    }

    /**
     * update database
     */
    public function remove()
    {

        global $osC_Database;
        $var = $osC_Database->query("
        DELETE FROM " . TABLE_CONFIGURATION . "
        WHERE configuration_key LIKE '%FEED_%'
        ");
        $var->execute();
    }


    /**
     * save data in database
     */

    public  $exportConfig = array(
        'FEED_TAX_RATE'           =>  '•• Tax Rate (Default)',
        'FEED_SHIPPING_COST'      =>  '•• Shipping Cost (Default)',
        'FEED_SIZE'               =>  '•• Size',
        'FEED_COLOR'              =>  '•• Color',
        'FEED_GENDER'             =>  '•• Gender',
        'FEED_MATERIAL'           =>  '•• Material',
        'FEED_EAN'                =>  '•• Ean',
        'FEED_GOOGLE'             =>  '•• Google',
        'FEED_ISBN'               =>  '•• ISBN',
        'FEED_BASE_UNIT'          =>  '•• Base Unit',
        'FEED_BASE_PRICE'         =>  '•• Base Price',
        'FEED_YATEGOO_CATEGORY'   =>  '•• Yategoo Category',
        'FEED_UVP'                =>  '•• Manufacturer recommended price'
    );

    /**
     *
     */
    public function install()
    {
        global $osC_Database ;

        foreach ($this->exportConfig as $feedField => $value) {
                $var = $osC_Database->query("
                    INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value)
                    VALUES ('" . $feedField . "','" . $value . "' )"
                );
            $var->execute();
        }
        echo 'inserted';
    }

    public function getOrdersProducts($currency, $orders_id){
        global $osC_Database ;
        $query = "select op.products_id, op.products_quantity, op.final_price from " . TABLE_ORDERS_PRODUCTS . " op where op.orders_id = ".$orders_id;
        $select = $osC_Database->query($query);
        $select->execute();
        $products = $this->fetch($select);

        return $products ;
    }

    public function getTrackingPixelStatus()
    {
        global $osC_Database;
        $db = $osC_Database ;
        $query = "select c.configuration_value from ".TABLE_CONFIGURATION. " c WHERE  c.configuration_key like '%FEED_TRACKING_PIXEL%' " ;
        $buff = $db->query($query);
        $buff->execute();
        $result = $this->fetch($buff);

        return $result ;
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

    public function getClientId(){
        global $osC_Database;
        $query = "SELECT t.configuration_value FROM  ".TABLE_CONFIGURATION."  t  WHERE t.configuration_key like '%FEED_CLIENT_NUMBER%'";
        $result = $osC_Database->query($query);
        $result->execute();
        $id = $this->fetch($result);

        return $id[0]["configuration_value"];
    }

    public function getOrderTotalValues($orders_id)
    {
        global $osC_Database;
        $result = $osC_Database->query("SELECT ot.value FROM ".TABLE_ORDERS_TOTAL." ot WHERE ot.orders_id = ".$orders_id." AND ot.title = 'Total:'");
        $result->execute();
        $value = $this->fetch($result);

        return $value[0]['value'];
    }

    public function getOrderId()
    {
        global $osC_Database ;
        $query = "SELECT o.orders_id from ".TABLE_ORDERS." o ORDER BY o.orders_id DESC " ;
        $result = $osC_Database->query($query);
        $order =  $this->fetch($result);

        return $order[0]['orders_id'];
    }

    public function getCurrency($orders_id)
    {
        global $osC_Database ;
        $query  =	$osC_Database->query(
            "SELECT o.currency
			 FROM ".TABLE_ORDERS." o
			 WHERE o.orders_id = ".$orders_id);
        $query->execute();
        $currency = $this->fetch($query);
        $currency = $currency[0]['currency'];

        return $currency ;
    }


}

