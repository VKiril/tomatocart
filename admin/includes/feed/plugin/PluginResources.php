<?php


class PluginResources {
    public $osC_Database ;
    public $prodAttrVal ;
    public $categories;

    public function __construct()
    {
        global $osC_Database ;
        $this->osC_Database = $osC_Database ;
        $this->getProductsAttributesValues();
        $this->getAllCategories();
    }

    protected function getProductsAttributesValues()
    {
        $temp = $this->osC_Database->query('
            SELECT
                pav.products_attributes_values_id,
                pav.name,
                pav.value
            FROM :prod_attr_values pav
        ');
        $temp->bindTable(":prod_attr_values", TABLE_PRODUCTS_ATTRIBUTES_VALUES);
        $temp->execute();
        $temp =  $this->fetch($temp);
        $buff = array();
        foreach ( $temp as $item) {
            $buff[$item['products_attributes_values_id']] = $item ;
        }
        $this->prodAttrVal = $buff ;
    }

    /**
     * @param $queryParameters
     * @param null $limit
     * @param int $offset
     * @param null $id
     * @return array
     */
    public function getProductsResources($queryParameters, $limit=null, $offset=0, $id = null)
    {
        $data = $this->getProductData($queryParameters, $limit, $offset, $id);
        return $data ;
    }


    /**
     * @param $queryParameters
     * @param $limit
     * @param $offset
     * @param $id
     * @return mixed
     */
    protected function getProductData($queryParameters, $limit, $offset, $id)
    {
        $products = $this->getProducts($queryParameters, $limit, $offset, $id) ;
        $productsIds = array();
        $productTempVal = array();
        foreach ($products as $item) {
            $productsIds []=$item['products_id'];
            $productTempVal[$item['products_id']] = $item;
        }
        $productsIds = implode(",", $productsIds);
        unset($temp);
        $imagesArray = $this->getImages($productsIds);

        foreach ($productTempVal as $item) {
            $productTempVal[$item['products_id']]['images'] = $imagesArray[$item['products_id']];
        }
        unset($temp);

        //attributes ids
        $attr = array();
        //attributes values ids
        $attrVals = array();
        foreach ($this->prodAttrVal as $key=>$value) {
            $attr[$value['name']][]= $key ;
            $attrVals[$value['value']] = $key ;
        }

        unset($temp);
        $temp = array();
        $key = '';
        $productsAttributes = $this->getProductAttributes($productsIds) ;
        foreach ($productsAttributes as $elementKey=>$element) {
            foreach ($element as $item) {
                if($key == $this->prodAttrVal[$item['products_attributes_values_id']]['name']){
                    $temp[$elementKey][$key][$this->prodAttrVal[$item['products_attributes_values_id']]['products_attributes_values_id']] =
                        $this->prodAttrVal[$item['products_attributes_values_id']]['value'] ;
                } else {
                    $key = $this->prodAttrVal[$item['products_attributes_values_id']]['name'];

                    $temp[$elementKey][$key][$this->prodAttrVal[$item['products_attributes_values_id']]['products_attributes_values_id']] =
                        $this->prodAttrVal[$item['products_attributes_values_id']]['value'] ;
                }
            }
        }
        $allCombinations = array();
        foreach ($temp as $itemKey=>$item) {
            if($itemKey == ''){
                $allCombinations = null ;
                break ;
            }

            $allCombinations[$itemKey] = $this->allCombinations($item);
        }

        $result['products'] = $productTempVal;
        $result['attrValues'] = $attrVals ;
        $result['allCombinations'] = $allCombinations ;

        return $result ;
    }

    /**
     * @param $queryParameters
     * @param $limit
     * @param $offset
     * @param $id
     * @return mixed
     */
    public function getProducts($queryParameters, $limit, $offset, $id)
    {
        $query = "
            SELECT DISTINCT
                    p.*,
                    m.manufacturers_name,
                    pd.*

                FROM       ".TABLE_PRODUCTS.            " p
                INNER JOIN ".TABLE_MANUFACTURERS.       " m on m.manufacturers_id = p.manufacturers_id
                INNER JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd on pd.products_id = p.products_id

                ";

//            0 => 'export_all_products',
//            1 => 'export_active_products',
//            2 => 'export_products_in_stock',
//            3 => 'export_active_products_in_stock',

        switch($queryParameters->status){
            case 1:
                $query .=" WHERE p.products_quantity > 0 " ;
                break;
            case 2:
                $query .=" WHERE p.products_status = 1 " ;
                break;
            case 3:
                $query .=" WHERE p.products_quantity > 0 AND WHERE p.products_status = 1  " ;
                break;
        }

        if($id != null){
            $query .= "AND  p.products_id=".$id;
        }
        $query .= " ORDER BY p.products_id ";
        if($limit != null ){
            $query .= "
                LIMIT ".$limit."
                OFFSET ".$offset;
        }


        $temp = $this->osC_Database->query($query);
        $temp->execute();
        $products = $this->fetch($temp);

        return $products ;
    }

    /**
     * @param $productsIds
     * @return array
     */
    public function getProductAttributes($productsIds)
    {
        $temp = $this->osC_Database->query("
            SELECT
                pa.products_id,
                pa.products_attributes_values_id
            FROM :productAttributes pa
            WHERE products_id IN (".$productsIds.")
        ");
        $temp->bindTable(":productAttributes", TABLE_PRODUCTS_ATTRIBUTES);
        $temp->execute();
        $productsAttributes = $this->fetch($temp);
        $buff = '';
        $buffArray = array();
        foreach ($productsAttributes as $item) {
            if($buff != $item){
                $buff = $item['products_id'] ;
                $buffArray[$buff][] = $item['products_attributes_values_id'];
            } else {
                $buffArray[$buff][] = $item['products_attributes_values_id'];
            }
        }

        return $buffArray ;
    }


    /**
     * @param $productsIds
     * @return array
     */
    public function getImages($productsIds)
    {
        $temp = $this->osC_Database->query("
            SELECT
                pi.products_id,
                pi.id,
                pi.image
            FROM  ".TABLE_PRODUCTS_IMAGES."  pi
            WHERE pi.products_id IN (".$productsIds.")
        ");
        $temp->execute();
        $images = $this->fetch($temp);
        $imagesArray = array();
        $id = 0 ;
        foreach ($images as $item) {
            if($id != $item['products_id']){
                $id = $item['products_id'];
                $imagesArray[$id][] = $item;
            } else {
                $imagesArray[$id][] = $item;
            }
        }

        return $imagesArray ;
    }

    /**
     * @param $arrays
     * @return array
     */
    function allCombinations($arrays)
    {
        $result = array();
        $arrayKeys = array_keys($arrays);
        $arrays = array_values($arrays);
        $sizeIn = sizeof($arrays);
        $size = $sizeIn > 0 ? 1 : 0;
        foreach ($arrays as $array)
            $size = $size * sizeof($array);
        for ($i = 0; $i < $size; $i++) {
            $result[$i] = array();
            for ($j = 0; $j < $sizeIn; $j++)
                array_push($result[$i], current($arrays[$j]));
            for ($j = ($sizeIn - 1); $j >= 0; $j--) {
                if (next($arrays[$j]))
                    break;
                elseif (isset ($arrays[$j]))
                    reset($arrays[$j]);
            }
        }
        $temp = array();
        foreach ($result as $key1 => $item) {
            foreach ($item as $key2 => $element) {
                $temp[$key1][$arrayKeys[$key2]] = $element;
            }
        }

        return $temp;
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
     * @param $table
     * @return array
     */
    public function getTableFields($table)
    {
        $var = $this->osC_Database->query("
            SELECT DISTINCT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '" . $table . "'
        ");
        $var->execute();
        $buff = $this->fetch($var);
        $tableColumns = array();
        foreach ($buff as $key => $value) {
            $tableColumns[$value['COLUMN_NAME']] = $value['COLUMN_NAME'];
        }

        return $tableColumns ;
    }

    /**
     * @param $prodId
     * @return string
     */
    public function getCoupon($prodId)
    {
        $coupon = $this->osC_Database->query('
            SELECT
                cd.coupons_name
            FROM '.TABLE_COUPONS_DESCRIPTION.'   cd
            INNER JOIN '.TABLE_COUPONS_TO_PRODUCTS.' cp on cp.coupons_id = cd.coupons_id
            WHERE cp.products_id = '.$prodId.'
        ');


        $coupon->execute();
        $coupon = $this->fetch($coupon);
        $test  = array();
        foreach ($coupon as $element) {
            $test[] = $element['coupons_name'];
        }
        $result = implode('; ',$test);

        return $result ;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $query = "
            SELECT
                pav.products_attributes_values_id,
                pav.name,
                pav.value
            FROM :productAttributeValue pav
        ";
        $attr = $this->osC_Database->query($query);
        $attr->bindTable(":productAttributeValue", TABLE_PRODUCTS_ATTRIBUTES_VALUES) ;
        $attr->execute();
        $attr = $this->fetch($attr);

        $buff = array();
        foreach ($attr as $item) {
            $temp = $item['products_attributes_values_id'];
            unset($item['products_attributes_values_id']) ;
            $buff[$temp] = $item ;
        }

        return $buff ;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOrder($id)
    {
        $query = "
            SELECT
                op.products_id,
                op.final_price,
                op.products_quantity
            FROM :order_products op
            WHERE op.orders_id = ".$id ;
        $order = $this->osC_Database->query($query);
        $order->bindTable(':order_products', TABLE_ORDERS_PRODUCTS);

        $order->execute();
        $order = $this->fetch($order);

        return $order ;
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