<?php

class Feed
{
    /** @var  PluginConfig */
    protected $shopContainer;

    protected static $_instance;

    /** @var  FeedPlugin */
    public $plugin = null;

    /** @var  FeedApi */
    public $api;

    /** @var $eventManager FeedEventManager */
    public $eventManager;

    /*
     * return Feed
     */
    public static function getInstance($sPluginPath = '', $shopContainer = null)
    {
        if (!self::$_instance[$sPluginPath] instanceof Feed) {
            self::$_instance[$sPluginPath] = new Feed($sPluginPath, $shopContainer);
        }

        return self::$_instance[$sPluginPath];
    }

    /**
     * @param $shopContainer
     */
    public function setShopContainer($shopContainer)
    {
        $this->shopContainer = $shopContainer;
    }

    public function getShopContainer()
    {
        return $this->shopContainer;
    }

    /**
     * @param string $sPluginPath
     * @param null $shopContainer
     * @throws Exception
     */
    protected function __construct($sPluginPath = '', $shopContainer = null)
    {
        $this->setShopContainer($shopContainer);
        $this->setPluginName($sPluginPath);
        $this->_initEventManager();
        $this->_initAPI();
    }

    /**
     * @param $request
     * @return mixed
     */
    public function dispatch($request)
    {

        if (!isset($request['secret']) || !$this->_checkSecurity($request['secret'])) {
            header('HTTP/1.0 401 Unauthorized');
            exit("401");
        }

        if (!isset($request["fnc"])) {
            header('HTTP/1.0 404 Not Found');
            exit("404");
        }
        $FeedCore = new FeedDispatcher($this);

        return $FeedCore->dispatch($request);
    }

    /**
     * @param string $sPluginPath
     * @return FeedPlugin
     * @throws Exception
     */
    public function setPluginName($sPluginPath = '')
    {
        if (empty($sPluginPath)) {
            throw new Exception("No plugin name defined");
        }

        if (file_exists($sPluginPath)) {
            include_once($sPluginPath);
            try {
                $this->plugin = new FeedConnector($this);
            } catch (Exception $e) {
                throw new Exception("Can't create connector", 0, $e);
            }
            if (!$this->plugin instanceof FeedPlugin) {
                throw new Exception("Plugin should implement FeedPlugin interface");
            }

            return $this->plugin;
        }
        throw new Exception("Plugin isn't found in feed/plugin directory");
    }


    protected function _initEventManager()
    {
        $this->eventManager = new FeedEventManager();
        $this->eventManager->addEventListener("onRegisterFeed", new FeedBaseListener($this));
        $this->eventManager->addEventListener("onNewsFeed", new FeedBaseListener($this));
    }

    protected function _initAPI()
    {
        $this->api = new FeedApi($this);
    }

    protected function _checkSecurity($security = '')
    {
        $myCode = md5($this->plugin->getApiSecret() . $this->plugin->getApiUsername());
        if ($myCode == $security) {
            return true;
        }
        header('HTTP/1.0 401 Unauthorized');
        exit();
    }

    /**
     * @param FeedApi $api
     */
    public function setApi($api)
    {
        $this->api = $api;
    }

    /**
     * @return FeedApi
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param FeedEventManager $eventManager
     */
    public function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @return FeedEventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @param FeedPlugin $plugin
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return FeedPlugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
}
