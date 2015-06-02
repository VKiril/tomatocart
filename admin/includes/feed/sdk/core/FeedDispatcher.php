<?php

class FeedDispatcher
{
    /**
     * @var Feed
     */
    protected $container;

    /**
     * @param Feed $container
     */
    public function __construct(Feed $container)
    {
        $this->container = $container;
    }

    /**
     * @param Feed $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return Feed
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function dispatch(array $request)
    {
        //var_dump($request);die;
        $fnc = $request["fnc"];
        if (method_exists($this, $fnc)) {
            if (isset($request["args"])) {
                $return = $this->$fnc($request["args"]);
            } else {
                $return = $this->$fnc(null);
            }
        } else {
            header('HTTP/1.0 501 Not Implemented');
            $return = false;
        }

        return $return;
    }

    /**
     * @param null $args
     * @return string
     */
    protected function getShopName($args = null)
    {
        $name = $this->container->plugin->getShopName();

        return $name;
    }

    /**
     * @param null $args
     * @return string
     */
    protected function getConfig($args = null)
    {
        header('Content-Type: json');
        $config = $this->container->plugin->getShopConfig();

        return json_encode($config);
    }

    /**
     * /**
     * @param null $args
     * @return stdClass
     */
    protected function getFeed($args = null)
    {
        $feedFields = $this->container->getApi()->getFeedFields();
        $actualConfig = $this->container->getApi()->getActualConfig();
        if (empty($feedFields) || $feedFields instanceof HttpResponse || empty($actualConfig) || $actualConfig instanceof HttpResponse) {
            header('HTTP/1.0 404 Not Found');

            return false;
        }
        $result = $this->container->plugin->getFeed($actualConfig, $feedFields);

        return $result;
    }

    /**
     * @param null $args
     * @return string
     */
    protected function getFields($args = null)
    {
        header('Content-Type: json');
        $fields = $this->container->plugin->getShopFields();

        return json_encode($fields);
    }

    /**
     * @param null $args
     * @return mixed|string
     */
    protected function getBridgeUrl($args = null)
    {
        $bridgeUrl = $this->container->plugin->getBridgeUrl();

        return $bridgeUrl;
    }

    /**
     * @param null $args
     * @return string
     */
    protected function getFeedUrl($args = null)
    {
        $feedFields = $this->container->getApi()->getFeedFields();
        $actualConfig = $this->container->getApi()->getActualConfig();
        $feedUrl = $this->container->plugin->getFeedUrl($actualConfig, $feedFields);

        return $feedUrl;
    }

    /**
     * @param null $args
     * @return \stdClass
     */
    protected function getDelta($args = null)
    {
//        $feedFields = $this->container->getApi()->getFeedFields();
//        $actualConfig = $this->container->getApi()->getActualConfig();
//        $delta = $this->container->plugin->getDelta($feedFields, $actualConfig, $args);
//        return $delta;
    }

    /**
     * @param null $args
     * @return \stdClass
     */
    protected function getOrders($args = null)
    {
        $orders = $this->container->plugin->getOrders($args);
        return $orders;
    }

    /**
     * @param null $args
     * @return string
     */
    protected function getOrdersUrl($args = null)
    {
//        $ordersUrl = $this->container->plugin->getOrdersUrl($args);
//        return $ordersUrl;
    }

    /**
     * @param null|array $args
     * @return mixed
     */
    protected function getProduct($args = null)
    {
        $feedFields = $this->container->getApi()->getFeedFields();
        $actualConfig = $this->container->getApi()->getActualConfig();
        if (!$args["id"] || empty($feedFields)) {
            header('HTTP/1.0 404 Not Found');

            return false;
        }
        $productInfo = $this->container->plugin->getProductInfo($actualConfig, $feedFields, $args["id"]);
        header('Content-Type: json');

        return json_encode($productInfo);
    }

    /**
     * @param null $args
     * @return mixed|string
     */
    protected function getOrderProducts($args = null)
    {
        if (!$args["id"]) {
            header('HTTP/1.0 404 Not Found');

            return false;
        }

        $actualConfig = $this->container->getApi()->getActualConfig();

        $orderProducts = $this->container->plugin->getOrderProducts($actualConfig, $args["id"]);
        header('Content-Type: json');

        return json_encode($orderProducts);
    }

    /**
     * @param null $args
     * @return mixed|string
     */
    protected function getFeatures($args = null)
    {
        $features = $this->container->plugin->getFeatures();
        header('Content-Type: json');

        return json_encode($features);
    }
}
