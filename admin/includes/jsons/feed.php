<?php
/*
  $Id: configuration_wizard.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

class toC_Json_Feed {
    var $configuration;

    function loadFeed()
    {
        $response              = array();
        $connectionSuccess     = 'Connection Success!';
        $connectionFailure     = 'Connection Fail!';
        $mainPath              = dirname(dirname(__FILE__));
        $connectionState       = true;
        $response['FORM_CODE'] = '';
        $connectionSuccessMessage = 'You are connected ';
        $connectionFailureMessage = 'Fail to connect  ';

        if( !isset($data) ) {
            $data = toC_Json_Feed::_getConnectionDataKeys();
        }
        foreach($_POST as $feedField=>$value) {
            if(strpos($feedField,'FEED_') !== false) {
                if($value != "--empty--" and $value != "")
                $data[$feedField] = $value;
            }
        }

        if(!$data['FEED_TRACKING_PIXEL_STATUS']) {
            $data['FEED_TRACKING_PIXEL_STATUS'] = false;
            $data['FEED_CLIENT_NUMBER'] = '--no_client--';
        }
        //end filter

        foreach( self::_getConnectionDataKeys() as $key=>$value ) { //check if all fields was filled
            $connectionState = ($data[$key] == '') ? false : true ;
            if($connectionState === false) { break; }
        }

        if($connectionState){

            toC_Json_Feed::_remove($data);
            toC_Json_Feed::_install($data);

            $sPath = $mainPath."/feed/sdk/feed.php";
            require_once($sPath);

            $sPluginName = "toC_plugin";
            $sPluginPath = $mainPath."/feed/plugin/".$sPluginName.".php";
            $oRegisterEvent = new FeedEvent();
            $oNewsEvent = new FeedNewsEvent();
            Feed::getInstance($sPluginPath)->eventManager->dispatchEvent("onRegisterFeed", $oRegisterEvent);
            Feed::getInstance($sPluginPath)->eventManager->dispatchEvent("onNewsFeed", $oNewsEvent);

            if($oRegisterEvent->getResponse()->getStatus() == 204) {
                $blCheckOK = true;
                $FeedNews = $oNewsEvent->getNews();
                $response['FORM_CODE'][] = $connectionSuccess;
                $response['FORM_CODE'][] = $connectionSuccessMessage;
            } else {
                $response['FORM_CODE'][] = $connectionFailure;
                $response['FORM_CODE'][] = $connectionFailureMessage;
                $blCheckError = true;
                $FeedError = $oRegisterEvent->getResponse()->getStatusMsg();
            }

        } else {
            $error = 'error';
            $response['FORM_CODE'][] = 'Error';
            $response['FORM_CODE'][] = 'Some fields are required!';
            $blCheckError = false;
        }

        //filling data to response the initial form
        foreach($data as $key=>$value) {
            $response[$key] = toC_Json_Feed::_getConfigurationToForm($key);
        }
        $response['FEED_DTIME_TYPE'] = toC_Json_Feed::_getConfigurationToForm('FEED_DTIME_TYPE');
        $response['FEED_DTIME_FROM'] = toC_Json_Feed::_getConfigurationToForm('FEED_DTIME_FROM');
        $response['FEED_DTIME_TO'] = toC_Json_Feed::_getConfigurationToForm('FEED_DTIME_TO');
        $response['FEED_TAX_ZONE'] = toC_Json_Feed::_getConfigurationToForm('FEED_TAX_ZONE');

        $response['ZONES']              =  toC_Json_Feed::_getTaxZones();
        $response['SHIPPING_MODULES']   = toC_Json_Feed::_getEnabledShippingModules();
        echo json_encode($response);
    }

    private static function getAllFeedData(){
        global $osC_Database;
        $var = $osC_Database->query("
            SELECT c.configuration_key, c.configuration_value
            FROM :configuration c
            WHERE c.configuration_key LIKE '%FEED%'
            ");
        $var->bindTable(':configuration', TABLE_CONFIGURATION);

        $var->execute();
        $buff = $self::fetch($var);
        $feed_conf = array();
        foreach ($buff as $elements) {
            $feed_conf[$elements['configuration_key']] = $elements['configuration_value'];
        }

        return $feed_conf ;
    }



    protected static function _remove()
    {
        $remove = $GLOBALS['osC_Database']->query(
            "DELETE FROM " . TABLE_CONFIGURATION . "
            WHERE configuration_key LIKE 'FEED_%'"
        );

        $remove->execute();
    }

    /**
     * @param $data
     */
    protected static function _install($data)
    {
        foreach($data as $key=>$value){
            $install = $GLOBALS['osC_Database']->query(
                "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value)
                 VALUES ('". $key ."','" . $value ."')"
            );

            $install->execute();
        }

    }

    /**
     * @param $string
     * @return mixed
     */
    protected static function _getConfigurationToForm($string)
    {
        $data = $GLOBALS['osC_Database']->query( "SELECT configuration_value FROM :table_configuration WHERE configuration_key = :configuration_key" );
        $data->bindTable(':table_configuration', TABLE_CONFIGURATION);
        $data->bindValue(':configuration_key', $string);
        $data->execute();
        $outputValue = $data->value('configuration_value');

        return $outputValue;
    }

    /**
     * @return array
     */
    protected static function _getTaxZones()
    {
        $i = 0; $output = array(); $temp = array();
        $data = $GLOBALS['osC_Database']->query( "SELECT geo_zone_id, geo_zone_name FROM ".TABLE_GEO_ZONES );
        $data->execute();
        while($i < $data->numberOfRows()) {
            $temp['id']   = $data->value('geo_zone_id');
            $temp['name'] = $data->value('geo_zone_name');
            $output[] = $temp;
            $data->next(); $i++;
        }

        return $output;
    }

    /**
     * @return array
     */
    protected static function _getEnabledShippingModules()
    {
        $i = 0; $output = array(); $temp = array();
        $data = $GLOBALS['osC_Database']->query( "select title, code from ".TABLE_TEMPLATES_BOXES." where modules_group = 'shipping'" );
        $data->execute();
        while($i < $data->numberOfRows()) {

            $temp['id']   = $data->value('code');
            $temp['name'] = $data->value('title');
            $output[] = $temp;
            $data->next(); $i++;
        }

        return $output;
    }

    /**
     * @return array
     */
    protected static function _getConnectionDataKeys()
    {
        return $data = array(
            'FEED_USER'                     => '',
            'FEED_PASSWORD'                 => '',
            'FEED_CLIENT_NUMBER'            => '',
            'FEED_SECRET'                   => '',
        );
    }

    /**
     *
     * the same steps like at symfony
     */
    protected static function fetch($query){
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

