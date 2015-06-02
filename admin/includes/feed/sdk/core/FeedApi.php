<?php

class FeedApi
{
    /**
     * @var HttpClient
     */
    protected $_client;

    protected $_request;

    protected $_response;

    protected $_plugin;

    protected $version = "1.0";

    /** @var Feed */
    protected $_container;

    /** @var string */
    protected $user;

    /** @var string */
    protected $password;

    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    protected function _defaultHeader()
    {
        $header = array(
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            "Cache-Control" => "no-cache",
        );

        return $header;
    }

    public function __construct(Feed $container)
    {
        $this->_plugin = $container->plugin;
        if (!$this->_plugin instanceof FeedPlugin) {
            throw new Exception("No plugin found");
        }
        $this->user = $this->_plugin->getApiUsername();
        $this->password = $this->_plugin->getApiPassword();
        $this->_container = $container;
        $this->_client = new HttpClient();
    }

    public function getAction($version)
    {
        if (!$this->_plugin instanceof FeedPlugin) {
            return false;
        }
        $request = new HttpRequest();
        $request->setUrl(FEED_API_URL . "/action/list?version=$version");
        $request->setHeaders($this->getSignHeader());
        $request->setHeaders($this->_defaultHeader());
        $request->setMethod('GET');
        /** @var HttpResponse $response */
        $response = $this->_client->doRequest($request);

        return $response;
    }

    /**
     * @return bool|null
     */
    public function registerUser()
    {
        if (!$this->_plugin instanceof FeedPlugin) {
            return false;
        }
        $type = "pluginRegister";
        $response = $this->getAction($this->getVersion());
        $body = json_decode($response->getBody());
        if (!isset($body->$type)) {
            return $response;
        } else {
            $url = $body->$type;
        }

        $secret = $this->_plugin->getApiSecret();
        $request = new HttpRequest();
        $request->setUrl($url);
        $request->setHeaders($this->getSignHeader());
        $request->setHeaders($this->_defaultHeader());
        $request->setHeaders(array("X-HTTP-Method-Override" => "PUT"));
        $request->setHeaders(array("Expect" => ''));
        $request->setMethod('PUT');
        $request->setBody(
            array(
                "secret"            => $secret,
                "bridgeUrl"         => $this->_plugin->getBridgeUrl(),
                "urlParams"         => $this->_plugin->getUrlParameters(),
                "possibleConfig"    => $this->_plugin->getShopConfig(),
                "possibleFields"    => $this->_plugin->getShopFields(),
                "shopName"          => $this->_plugin->getShopName()
            )
        );

        /** @var HttpResponse $response */
        $response = $this->_client->doRequest($request);

        return $response;
    }

    public function getLastNews()
    {
        if (!$this->_plugin instanceof FeedPlugin) {
            return false;
        }
        $type = "pluginNews";
        $response = $this->getAction($this->getVersion());
        $body = json_decode($response->getBody());
        if (!isset($body->$type)) {
            return $response;
        } else {
            $url = $body->$type;
        }

        $request = new HttpRequest();
        $request->setUrl($url);
        $request->setHeaders($this->getSignHeader());
        $request->setHeaders($this->_defaultHeader());
        $request->setMethod("GET");
        $this->_client->doRequest($request);
        $response = $this->_client->doRequest($request);

        return $response;
    }

    public function getFeedFields()
    {
        if (!$this->_plugin instanceof FeedPlugin) {
            return false;
        }
        $request = new HttpRequest();

        $type = "pluginFields";
        $response = $this->getAction($this->getVersion());
        $body = json_decode($response->getBody());
        if (!isset($body->$type)) {
            return $response;
        } else {
            $url = $body->$type;
        }

        $request->setUrl($url);
        $request->setHeaders($this->getSignHeader());
        $request->setHeaders($this->_defaultHeader());
        $request->setMethod('GET');

        /** @var HttpResponse $response */
        $response = $this->_client->doRequest($request);
        $body = json_decode($response->getBody(), true);

        return $body;
    }

    public function getActualConfig()
    {
        if (!$this->_plugin instanceof FeedPlugin) {
            return false;
        }
        $request = new HttpRequest();

        $type = "pluginConfig";
        $response = $this->getAction($this->getVersion());
        $body = json_decode($response->getBody());
        if (!isset($body->$type)) {
            return $response;
        } else {
            $url = $body->$type;
        }

        $request->setUrl($url);
        $request->setHeaders($this->getSignHeader());
        $request->setHeaders($this->_defaultHeader());
        $request->setMethod('GET');

        /** @var HttpResponse $response */
        $response = $this->_client->doRequest($request);
        $body = json_decode($response->getBody());

        return $body;
    }

    public function getSignHeader()
    {
        $header = array('Authorization' => "Basic " . base64_encode($this->getUser() . ":" . $this->getPassword()));

        return $header;
    }
}
