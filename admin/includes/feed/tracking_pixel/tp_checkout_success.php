<?php
	require_once DIR_FS_CATALOG . "admin/includes/feed/config/Config.php";

    global $osC_Database;
    $db = $osC_Database ;
    $result = $db->query("SELECT configuration_value FROM ".TABLE_CONFIGURATION." WHERE configuration_key = 'FEED_TRACKING_PIXEL_STATUS'")->execute();
    $pixelActive = $result->fields['configuration_value'];
    if ($pixelActive === 'Y'){ // && isset($_COOKIE['_fr'])) {
		$config = new Config();
		//$config->iniParameters();

        $result = $db->query("SELECT configuration_value FROM ".TABLE_CONFIGURATION." WHERE configuration_key = 'FEED_CLIENT_ID'")->execute();
        $feedClientId = $result->fields['configuration_value'];
        $result = $db->query("SELECT value FROM ".TABLE_ORDERS_TOTAL." WHERE orders_id = ".$orders_id." AND title = 'Total:'")->execute();
        $orderSum = $result->fields['value'];

        $result  =	$db->query(
			"SELECT o.currency,
					c.currencies_id
			FROM ".TABLE_ORDERS." o

			LEFT JOIN ".TABLE_CURRENCIES." c
			ON (c.code = o.currency)

			WHERE o.orders_id = ".$orders_id
		)->execute();

        $currency = $result->fields['currency'];

		$products = '';

		$productsData = $config->getOrdersProducts($result->fields['currencies_id'], $orders_id, false, true);
        //var_dump($productsData);die;
		foreach ($productsData as $product) {
			//$tax = zen_get_tax_rate($product['tax_class_id'], $config->taxZone['zone_country_id'], $config->taxZone['zone_id']);
			$price = $product['product']['price'];
            $quantity = $product['product']['qty'] ;
            $products .= $product['attributes']['ModelOwn']."=".$price."=".$quantity.";";
		}
?>
        <script type="text/javascript">
            var _feeparams = _feeparams || new Object();
            //Required clientId
            console.log('testare tracking');
            _feeparams.client = '<?php echo isset($feedClientId) ? $feedClientId : ''; ?>';
            //Required tracking type
            _feeparams.event = 'sale';
            //Required for tracking the sales (your internal orderID)
            _feeparams.orderid = '<?php echo $orders_id ?>';
            //Required for tracking the sales (order sum)
            _feeparams.ordersum = '<?php echo $orderSum ?>';
            //Required for tracking the sales (order currency)
            _feeparams.ordercur = '<?php echo $currency ?>';
            //Optional you can add product information for better statistics product_code_1=sum_1=qty_1;product_code_2=sum_2=qty_2
            //Product code is the code you put to import feed for Feed (unique product identifier)
            //the sum for particular product decimal seperator "."
            //Quantity the amount of particular products in order integer values default is 1
            _feeparams.products = '<?php echo $products ?>';
            //Additional parameters
            _feeparams.sparam = '';
            (function () {
                //console.log(_feeparams);
                var head = document.getElementsByTagName('head')[0];
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = (location.protocol == "https:" ? "https:" : "http:") + '//daily-feed.com/bundles/managementtracking/js/pixel.js';
                // fire the loading
                head.appendChild(script);
            })();
			console.log(_feeparams);
        </script>
<?php } ?>
