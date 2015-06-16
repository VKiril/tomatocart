<?php

if(isset($products_array)){
    require_once DIR_FS_CATALOG . "admin/includes/feed/config/Config.php";

    $config = new Config();
    $tp = $config->getTrackingPixelStatus();
    $pixelActive = $tp[0]["configuration_value"];

    if ($pixelActive == 'Y'){ // && isset($_COOKIE['_fr'])) {
        $config = new Config();
        $feedClientId = $config->getClientId();
        $orders_id = $config->getOrderId();
        $orderSum = $config->getOrderTotalValues($orders_id);
        $currency = $config->getCurrency($orders_id);
        $productsData = $config->getOrdersProducts($currency, $orders_id);
        $products = "";
        $flag  = 0 ;
        foreach ($productsData as $key=>$product) {
            if($flag == 0){
                $flag = 1 ;
                $products .= "product_code_".($key+1)."=".$product["products_id"] . "sum_".($key+1)."=".$product["final_price"] . "qty_".($key+1)."=".$product["products_quantity"];
            } else {
                $products .= ";product_code_".($key+1)."=".$product["products_id"] . "sum_".($key+1)."=".$product["final_price"] . "qty_".($key+1)."=".$product["products_quantity"];
            }
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
                var head = document.getElementsByTagName('head')[0];
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = (location.protocol == "https:" ? "https:" : "http:") + '//daily-feed.com/bundles/managementtracking/js/pixel.js';
                // fire the loading
                head.appendChild(script);
            })();
        </script>
    <?php
    }

}

