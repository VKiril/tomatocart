<?php

    if (!isset($_REQUEST['dataFeed'])) {
        if (isset($_REQUEST['dataExport'])) {
            header('Location: http://daily-feed.com/export/' . $_REQUEST['dataExport']);
        } else {
            header('HTTP/1.0 404 Not Found');
            echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
        }
    } else {

        require_once('includes/application_top.php');
        $sPath =  dirname($_SERVER['SCRIPT_FILENAME'])."/admin/includes/feed/sdk/feed.php";

        if(file_exists($sPath)) {

            require_once($sPath);
            $sPluginName = "toC_plugin";
            $sPluginPath = dirname($_SERVER['SCRIPT_FILENAME']).'/admin/includes/feed/plugin/'.$sPluginName.".php";

            /**
             * @var $oFeed Feed
             */
            $oFeed = Feed::getInstance($sPluginPath);
            $request = $_REQUEST['feed'];
            $response = $oFeed->dispatch($request);
            if ($request["fnc"] != "getFeed") {
                $response = (is_null(json_decode($response))) ? $response : json_decode($response);
                print_r($response);
            }
            exit();
        }
        header("HTTP/1.0 404 Not Found");
        exit();
    }

